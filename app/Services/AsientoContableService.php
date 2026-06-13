<?php

namespace App\Services;

use App\Models\AsientoContable;
use App\Models\CuentaContable;
use App\Models\MovimientoInventario;
use App\Models\PedidoCompra;
use App\Models\PedidoVenta;

/**
 * @deprecated Deuda técnica conocida (ver auditoría 2026-06).
 *
 * Este servicio NO está cableado a ningún evento ni flujo de la aplicación
 * de forma intencional: la contabilidad (asientos de venta, compra, costo de
 * venta y ajustes de inventario) la maneja el sistema externo E-Trib, no este
 * ERP. Los métodos quedan como referencia de la partida doble salvadoreña.
 *
 * TODO: NO conectar a observers/eventos sin antes confirmar con el área
 * contable que se desea migrar la generación de asientos desde E-Trib a este
 * sistema. Mientras tanto, mantener sin invocar.
 */
class AsientoContableService
{
    // Códigos del Plan de Cuentas
    const COD = [
        'caja' => '1.1.01',
        'bancos' => '1.1.02',
        'cxc_clientes' => '1.1.03',
        'iva_credito' => '1.1.04',
        'inventario' => '1.1.05',
        'cxp_proveedores' => '2.1.01',
        'iva_debito' => '2.1.02',
        'ventas' => '4.1.01',
        'costo_ventas' => '5.1.01',
    ];

    protected function cuenta(string $codigo): CuentaContable
    {
        return CuentaContable::where('codigo', $codigo)->firstOrFail();
    }

    // ─── Venta ────────────────────────────────────────────────────────────────
    // DÉBITO:  CxC Clientes         (total con IVA)
    // CRÉDITO: Ventas               (base imponible)
    // CRÉDITO: IVA Débito Fiscal    (IVA)
    public function fromPedidoVenta(PedidoVenta $pedido): AsientoContable
    {
        $asiento = AsientoContable::create([
            'numero' => AsientoContable::generarNumero(),
            'fecha' => $pedido->fecha_pedido ?? now(),
            'descripcion' => "Venta {$pedido->numero} — {$pedido->cliente->nombre}",
            'tipo_documento' => 'ccf',
            'numero_documento' => $pedido->numero,
            'estado' => 'registrado',
            'origen_tipo' => 'PedidoVenta',
            'origen_id' => $pedido->id,
            'user_id' => auth()->id(),
        ]);

        $asiento->lineas()->createMany([
            ['cuenta_contable_id' => $this->cuenta(self::COD['cxc_clientes'])->id,  'descripcion' => "CxC {$pedido->cliente->nombre}", 'debe' => $pedido->total,    'haber' => 0,                'orden' => 1],
            ['cuenta_contable_id' => $this->cuenta(self::COD['ventas'])->id,         'descripcion' => "Ventas {$pedido->numero}",        'debe' => 0,                 'haber' => $pedido->subtotal, 'orden' => 2],
            ['cuenta_contable_id' => $this->cuenta(self::COD['iva_debito'])->id,     'descripcion' => 'IVA Débito Fiscal 13%',          'debe' => 0,                 'haber' => $pedido->impuesto, 'orden' => 3],
        ]);

        $asiento->recalcularTotales();

        return $asiento;
    }

    // ─── Costo de Venta ───────────────────────────────────────────────────────
    // DÉBITO:  Costo de Ventas
    // CRÉDITO: Inventario de Mercadería
    public function costoVenta(PedidoVenta $pedido, float $costo): AsientoContable
    {
        $asiento = AsientoContable::create([
            'numero' => AsientoContable::generarNumero(),
            'fecha' => $pedido->fecha_pedido ?? now(),
            'descripcion' => "Costo de ventas {$pedido->numero}",
            'tipo_documento' => 'comprobante_interno',
            'numero_documento' => $pedido->numero,
            'estado' => 'registrado',
            'origen_tipo' => 'PedidoVenta',
            'origen_id' => $pedido->id,
            'user_id' => auth()->id(),
        ]);

        $asiento->lineas()->createMany([
            ['cuenta_contable_id' => $this->cuenta(self::COD['costo_ventas'])->id, 'descripcion' => 'Costo de ventas', 'debe' => $costo, 'haber' => 0,      'orden' => 1],
            ['cuenta_contable_id' => $this->cuenta(self::COD['inventario'])->id,   'descripcion' => 'Salida inventario', 'debe' => 0,      'haber' => $costo, 'orden' => 2],
        ]);

        $asiento->recalcularTotales();

        return $asiento;
    }

    // ─── Compra ───────────────────────────────────────────────────────────────
    // DÉBITO:  Inventario de Mercadería    (base imponible)
    // DÉBITO:  IVA Crédito Fiscal          (IVA)
    // CRÉDITO: CxP Proveedores             (total)
    public function fromPedidoCompra(PedidoCompra $pedido): AsientoContable
    {
        $asiento = AsientoContable::create([
            'numero' => AsientoContable::generarNumero(),
            'fecha' => $pedido->fecha_pedido ?? now(),
            'descripcion' => "Compra {$pedido->numero} — {$pedido->proveedor->nombre}",
            'tipo_documento' => 'ccf',
            'numero_documento' => $pedido->numero,
            'estado' => 'registrado',
            'origen_tipo' => 'PedidoCompra',
            'origen_id' => $pedido->id,
            'user_id' => auth()->id(),
        ]);

        $asiento->lineas()->createMany([
            ['cuenta_contable_id' => $this->cuenta(self::COD['inventario'])->id,      'descripcion' => 'Compra mercadería',                'debe' => $pedido->subtotal, 'haber' => 0,             'orden' => 1],
            ['cuenta_contable_id' => $this->cuenta(self::COD['iva_credito'])->id,     'descripcion' => 'IVA Crédito Fiscal 13%',           'debe' => $pedido->impuesto, 'haber' => 0,             'orden' => 2],
            ['cuenta_contable_id' => $this->cuenta(self::COD['cxp_proveedores'])->id, 'descripcion' => "CxP {$pedido->proveedor->nombre}", 'debe' => 0,                 'haber' => $pedido->total, 'orden' => 3],
        ]);

        $asiento->recalcularTotales();

        return $asiento;
    }

    // ─── Ajuste de Inventario ─────────────────────────────────────────────────
    public function fromMovimientoInventario(MovimientoInventario $movimiento): AsientoContable
    {
        $esEntrada = $movimiento->es_entrada;
        $valor = (float) $movimiento->costo_total;

        $asiento = AsientoContable::create([
            'numero' => AsientoContable::generarNumero(),
            'fecha' => $movimiento->fecha_movimiento ?? now(),
            'descripcion' => "Movimiento inventario {$movimiento->numero} — {$movimiento->tipo}",
            'tipo_documento' => 'comprobante_interno',
            'numero_documento' => $movimiento->numero,
            'estado' => 'registrado',
            'origen_tipo' => 'MovimientoInventario',
            'origen_id' => $movimiento->id,
            'user_id' => auth()->id(),
        ]);

        if ($esEntrada) {
            // DÉBITO Inventario / CRÉDITO Capital (ajuste positivo)
            $asiento->lineas()->createMany([
                ['cuenta_contable_id' => $this->cuenta(self::COD['inventario'])->id, 'descripcion' => 'Entrada inventario', 'debe' => $valor, 'haber' => 0,      'orden' => 1],
                ['cuenta_contable_id' => $this->cuenta(self::COD['costo_ventas'])->id, 'descripcion' => 'Contrapartida',      'debe' => 0,      'haber' => $valor, 'orden' => 2],
            ]);
        } else {
            // DÉBITO Costo de Ventas / CRÉDITO Inventario (salida/merma)
            $asiento->lineas()->createMany([
                ['cuenta_contable_id' => $this->cuenta(self::COD['costo_ventas'])->id, 'descripcion' => 'Salida/merma inventario', 'debe' => $valor, 'haber' => 0,      'orden' => 1],
                ['cuenta_contable_id' => $this->cuenta(self::COD['inventario'])->id,   'descripcion' => 'Inventario',              'debe' => 0,      'haber' => $valor, 'orden' => 2],
            ]);
        }

        $asiento->recalcularTotales();

        return $asiento;
    }
}
