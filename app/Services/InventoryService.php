<?php

namespace App\Services;

use App\Models\InventarioAlmacen;
use App\Models\MovimientoInventario;
use Illuminate\Database\Eloquent\Model;

/**
 * Fuente única de verdad para mutar stock por sucursal y registrar el kardex.
 *
 * Todo movimiento de inventario (ventas, traslados, ajustes) debe pasar por
 * aquí para garantizar que InventarioAlmacen.stock_actual y el kardex
 * (MovimientoInventario) queden siempre consistentes.
 */
class InventoryService
{
    /**
     * Aplica un delta de stock a (producto, almacén) y registra el kardex.
     * $delta negativo = salida; positivo = entrada.
     */
    public function registrarMovimiento(
        int $productoId,
        int $almacenId,
        float $delta,
        string $tipo,
        string $motivo,
        ?Model $referencia = null,
        ?float $costoUnitario = null,
        ?int $userId = null,
    ): MovimientoInventario {
        $inventario = InventarioAlmacen::firstOrCreate(
            ['producto_id' => $productoId, 'almacen_id' => $almacenId],
            ['stock_actual' => 0, 'stock_minimo' => 0, 'stock_maximo' => 0, 'punto_reorden' => 0],
        );

        $stockAnterior = (float) $inventario->stock_actual;
        $stockNuevo = $stockAnterior + $delta;

        $inventario->update(['stock_actual' => $stockNuevo]);

        $cantidad = abs($delta);

        return MovimientoInventario::create([
            'numero' => MovimientoInventario::generarNumero(),
            'producto_id' => $productoId,
            'almacen_id' => $almacenId,
            'user_id' => $userId ?? auth()->id(),
            'tipo' => $tipo,
            'cantidad' => $cantidad,
            'stock_anterior' => $stockAnterior,
            'stock_nuevo' => $stockNuevo,
            'costo_unitario' => $costoUnitario,
            'costo_total' => $costoUnitario !== null ? round($cantidad * $costoUnitario, 2) : null,
            'referencia_type' => $referencia?->getMorphClass(),
            'referencia_id' => $referencia?->getKey(),
            'fecha_movimiento' => now(),
            'motivo' => $motivo,
        ]);
    }

    /** Salida de stock (delta negativo). */
    public function salida(
        int $productoId,
        int $almacenId,
        float $cantidad,
        string $tipo,
        string $motivo,
        ?Model $referencia = null,
        ?float $costoUnitario = null,
        ?int $userId = null,
    ): MovimientoInventario {
        return $this->registrarMovimiento(
            $productoId, $almacenId, -abs($cantidad), $tipo, $motivo, $referencia, $costoUnitario, $userId
        );
    }

    /** Entrada de stock (delta positivo). */
    public function entrada(
        int $productoId,
        int $almacenId,
        float $cantidad,
        string $tipo,
        string $motivo,
        ?Model $referencia = null,
        ?float $costoUnitario = null,
        ?int $userId = null,
    ): MovimientoInventario {
        return $this->registrarMovimiento(
            $productoId, $almacenId, abs($cantidad), $tipo, $motivo, $referencia, $costoUnitario, $userId
        );
    }
}
