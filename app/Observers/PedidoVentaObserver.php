<?php

namespace App\Observers;

use App\Models\PedidoVenta;
use App\Models\MovimientoInventario;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class PedidoVentaObserver
{
    /**
     * Cuando el pedido pasa a "en_preparacion", reserva/descuenta stock.
     */
    public function updated(PedidoVenta $pedido): void
    {
        if (! $pedido->wasChanged('estado')) {
            return;
        }

        // Descontar stock cuando se pone "en_preparacion"
        if ($pedido->estado === 'en_preparacion' && $pedido->getOriginal('estado') === 'confirmado') {
            DB::transaction(function () use ($pedido) {
                foreach ($pedido->items as $item) {
                    $producto      = $item->producto;
                    $stockAnterior = $producto->stock_actual;

                    if ($stockAnterior < $item->cantidad) {
                        // Notificar si no hay suficiente stock
                        Notification::make()
                            ->title("Stock insuficiente: {$producto->nombre}")
                            ->body("Disponible: {$stockAnterior} | Requerido: {$item->cantidad}")
                            ->warning()
                            ->send();
                        continue;
                    }

                    $stockNuevo = $stockAnterior - $item->cantidad;

                    $producto->update(['stock_actual' => $stockNuevo]);

                    MovimientoInventario::create([
                        'numero'           => MovimientoInventario::generarNumero(),
                        'producto_id'      => $producto->id,
                        'user_id'          => auth()->id() ?? 1,
                        'tipo'             => 'salida_venta',
                        'cantidad'         => $item->cantidad,
                        'stock_anterior'   => $stockAnterior,
                        'stock_nuevo'      => $stockNuevo,
                        'costo_unitario'   => $item->precio_unitario,
                        'costo_total'      => $item->cantidad * $item->precio_unitario,
                        'referencia_type'  => PedidoVenta::class,
                        'referencia_id'    => $pedido->id,
                        'motivo'           => "Despacho OV {$pedido->numero}",
                        'fecha_movimiento' => now(),
                    ]);

                    $item->update(['cantidad_despachada' => $item->cantidad]);
                }
            });
        }
    }
}
