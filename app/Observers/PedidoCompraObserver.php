<?php

namespace App\Observers;

use App\Models\PedidoCompra;
use App\Models\MovimientoInventario;
use App\Models\InventarioAlmacen;

class PedidoCompraObserver
{
    public function updated(PedidoCompra $pedidoCompra)
    {
        // Cuando la orden cambia a "recibido"
        if ($pedidoCompra->wasDirty('estado') && $pedidoCompra->estado === 'recibido') {
            foreach ($pedidoCompra->items as $item) {
                // Buscar o crear inventario para el producto en el almacén principal
                $inventario = InventarioAlmacen::where('producto_id', $item->producto_id)
                    ->where('almacen_id', 1) // Almacén principal
                    ->first();
                
                $stockAnterior = $inventario ? $inventario->stock_actual : 0;
                $stockNuevo = $stockAnterior + $item->cantidad;
                
                if ($inventario) {
                    $inventario->stock_actual = $stockNuevo;
                    $inventario->save();
                } else {
                    $inventario = InventarioAlmacen::create([
                        'producto_id' => $item->producto_id,
                        'almacen_id' => 1,
                        'stock_actual' => $stockNuevo,
                        'stock_minimo' => $item->producto->stock_minimo,
                        'stock_maximo' => $item->producto->stock_maximo,
                    ]);
                }
                
                // Registrar movimiento
                MovimientoInventario::create([
                    'numero' => MovimientoInventario::generarNumero(),
                    'producto_id' => $item->producto_id,
                    'almacen_id' => 1,
                    'user_id' => auth()->id() ?? 1,
                    'tipo' => 'entrada_compra',
                    'cantidad' => $item->cantidad,
                    'stock_anterior' => $stockAnterior,
                    'stock_nuevo' => $stockNuevo,
                    'costo_unitario' => $item->precio_unitario,
                    'costo_total' => $item->subtotal,
                    'referencia_type' => PedidoCompra::class,
                    'referencia_id' => $pedidoCompra->id,
                    'fecha_movimiento' => now(),
                    'motivo' => "Recepción de OC {$pedidoCompra->numero}",
                ]);
            }
        }
    }
}