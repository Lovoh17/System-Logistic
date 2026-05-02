<?php

namespace App\Observers;

use App\Models\PedidoCompra;
use App\Models\MovimientoInventario;
use Illuminate\Support\Facades\DB;

class PedidoCompraObserver
{
    /**
     * Cuando se marca como "recibido", actualiza el inventario automáticamente.
     */
    public function updated(PedidoCompra $pedido): void
    {
        // Solo actuar cuando cambia a "recibido" o "parcial"
        if (! $pedido->wasChanged('estado')) {
            return;
        }

        if (! in_array($pedido->estado, ['recibido', 'parcial'])) {
            return;
        }

        DB::transaction(function () use ($pedido) {
            foreach ($pedido->items as $item) {
                $cantidadAIngresar = $item->cantidad - $item->cantidad_recibida;

                if ($cantidadAIngresar <= 0) {
                    continue;
                }

                $producto = $item->producto;
                $stockAnterior = $producto->stock_actual;
                $stockNuevo    = $stockAnterior + $cantidadAIngresar;

                // Actualizar stock del producto
                $producto->update(['stock_actual' => $stockNuevo]);

                // Registrar movimiento de inventario (Kardex)
                MovimientoInventario::create([
                    'numero'           => MovimientoInventario::generarNumero(),
                    'producto_id'      => $producto->id,
                    'user_id'          => auth()->id() ?? 1,
                    'tipo'             => 'entrada_compra',
                    'cantidad'         => $cantidadAIngresar,
                    'stock_anterior'   => $stockAnterior,
                    'stock_nuevo'      => $stockNuevo,
                    'costo_unitario'   => $item->precio_unitario,
                    'costo_total'      => $cantidadAIngresar * $item->precio_unitario,
                    'referencia_type'  => PedidoCompra::class,
                    'referencia_id'    => $pedido->id,
                    'motivo'           => "Recepción OC {$pedido->numero}",
                    'fecha_movimiento' => now(),
                ]);

                // Marcar cantidad recibida en el ítem
                $item->update(['cantidad_recibida' => $item->cantidad]);
            }
        });
    }
}
