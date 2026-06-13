<?php

namespace App\Services;

use App\Models\PedidoVenta;
use App\Models\PedidoVentaItem;
use Illuminate\Support\Facades\DB;

/**
 * Orquesta la creación de ventas (POS): crea el pedido y sus líneas y
 * descuenta inventario a través de InventoryService.
 *
 * No envía el recibo ni notifica: eso queda en la capa de UI (PuntoVenta),
 * que encola el correo tras confirmarse la transacción.
 */
class VentaService
{
    public function __construct(private InventoryService $inventory) {}

    /**
     * Procesa una venta de mostrador (entrega inmediata) y descuenta stock.
     *
     * @param  array<int, array{id:int, nombre:string, precio:float, cantidad:int|float, subtotal:float}>  $items
     */
    public function procesarVentaPos(int $clienteId, int $almacenId, array $items, ?int $userId = null): PedidoVenta
    {
        $userId ??= auth()->id();

        return DB::transaction(function () use ($clienteId, $almacenId, $items, $userId) {
            $subtotal = round(array_sum(array_column($items, 'subtotal')), 2);
            $impuesto = round($subtotal * 0.13, 2);
            $total = round($subtotal + $impuesto, 2);

            $pedido = PedidoVenta::create([
                'numero' => PedidoVenta::generarNumero(),
                'cliente_id' => $clienteId,
                'user_id' => $userId,
                'almacen_id' => $almacenId,
                'fecha_pedido' => now(),
                'estado' => 'entregado',
                'subtotal' => $subtotal,
                'impuesto' => $impuesto,
                'total' => $total,
                'moneda' => 'USD',
                'canal_venta' => 'directo',
            ]);

            foreach ($items as $item) {
                PedidoVentaItem::create([
                    'pedido_venta_id' => $pedido->id,
                    'producto_id' => $item['id'],
                    'cantidad' => $item['cantidad'],
                    'cantidad_despachada' => $item['cantidad'],
                    'precio_unitario' => $item['precio'],
                    'subtotal' => $item['subtotal'],
                ]);

                $this->inventory->salida(
                    $item['id'],
                    $almacenId,
                    (float) $item['cantidad'],
                    'salida_venta',
                    "Venta POS - {$pedido->numero}",
                    $pedido,
                    (float) $item['precio'],
                    $userId,
                );
            }

            return $pedido;
        });
    }
}
