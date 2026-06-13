<?php

namespace App\Observers;

use App\Models\PedidoVenta;
use App\Services\InventoryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Mueve inventario de un pedido de venta según transiciones de estado,
 * delegando toda mutación de stock + kardex a InventoryService.
 *
 *   → confirmado         : descuenta el stock del almacén del pedido (salida_venta)
 *   → cancelado/devolucion (desde un estado ya descontado): devuelve el stock
 *
 * El stock se descuenta una sola vez (al entrar en "confirmado") y permanece
 * descontado mientras el pedido avanza (en_preparacion, listo, ..., entregado).
 *
 * Nota: las ventas de POS se crean directamente como "entregado" y descuentan
 * vía VentaService, por lo que este observer (solo `updated`) no las duplica.
 */
class PedidoVentaObserver
{
    /** Estados en los que el stock ya fue descontado. */
    private const ESTADOS_DESCONTADO = [
        'confirmado', 'en_preparacion', 'listo', 'en_transito', 'entregado',
    ];

    public function __construct(private InventoryService $inventory) {}

    public function updated(PedidoVenta $pedido): void
    {
        if (! $pedido->wasChanged('estado')) {
            return;
        }

        $nuevo = $pedido->estado;
        $anterior = $pedido->getOriginal('estado');

        if (! $pedido->almacen_id) {
            Log::warning("PedidoVenta {$pedido->numero} sin almacén: no se mueve inventario.");

            return;
        }

        // Entra en estado descontado desde uno que no lo estaba → descontar.
        if ($nuevo === 'confirmado' && ! in_array($anterior, self::ESTADOS_DESCONTADO, true)) {
            $this->descontar($pedido);

            return;
        }

        // Cancelación/devolución desde un estado ya descontado → restaurar.
        if (in_array($nuevo, ['cancelado', 'devolucion'], true)
            && in_array($anterior, self::ESTADOS_DESCONTADO, true)) {
            $this->restaurar($pedido);
        }
    }

    private function descontar(PedidoVenta $pedido): void
    {
        DB::transaction(function () use ($pedido) {
            foreach ($pedido->items()->get() as $item) {
                $cantidad = (float) $item->cantidad;
                if ($cantidad <= 0) {
                    continue;
                }

                $this->inventory->salida(
                    $item->producto_id,
                    $pedido->almacen_id,
                    $cantidad,
                    'salida_venta',
                    "Salida por venta {$pedido->numero}",
                    $pedido,
                    (float) $item->precio_unitario,
                    auth()->id() ?? $pedido->user_id,
                );

                $item->update(['cantidad_despachada' => $cantidad]);
            }
        });
    }

    private function restaurar(PedidoVenta $pedido): void
    {
        DB::transaction(function () use ($pedido) {
            foreach ($pedido->items()->get() as $item) {
                $cantidad = (float) $item->cantidad;
                if ($cantidad <= 0) {
                    continue;
                }

                $this->inventory->entrada(
                    $item->producto_id,
                    $pedido->almacen_id,
                    $cantidad,
                    'devolucion_venta',
                    "Devolución por cancelación de venta {$pedido->numero}",
                    $pedido,
                    (float) $item->precio_unitario,
                    auth()->id() ?? $pedido->user_id,
                );

                $item->update(['cantidad_despachada' => 0]);
            }
        });
    }
}
