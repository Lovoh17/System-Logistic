<?php

namespace App\Observers;

use App\Models\Traslado;
use App\Models\MovimientoInventario;
use App\Models\InventarioAlmacen;

class TrasladoObserver
{
    public function created(Traslado $traslado)
    {
        $inventarioOrigen = InventarioAlmacen::where('producto_id', $traslado->producto_id)
            ->where('almacen_id', $traslado->almacen_origen_id)
            ->first();
        
        if ($inventarioOrigen) {
            $stockAnterior = $inventarioOrigen->stock_actual;
            $stockNuevo = $stockAnterior - $traslado->cantidad;
            
            $inventarioOrigen->stock_actual = $stockNuevo;
            $inventarioOrigen->save();
            
            MovimientoInventario::create([
                'numero' => MovimientoInventario::generarNumero(),
                'producto_id' => $traslado->producto_id,
                'almacen_id' => $traslado->almacen_origen_id,
                'user_id' => auth()->id() ?? 1,
                'tipo' => 'traslado_salida',
                'cantidad' => $traslado->cantidad,
                'stock_anterior' => $stockAnterior,
                'stock_nuevo' => $stockNuevo,
                'referencia_type' => Traslado::class,
                'referencia_id' => $traslado->id,
                'fecha_movimiento' => now(),
                'motivo' => "Traslado a sucursal #{$traslado->almacen_destino_id}",
            ]);
        }
    }
    
    public function updated(Traslado $traslado)
    {
        if ($traslado->isDirty('estado') && $traslado->estado === 'entregado') {
            $inventarioDestino = InventarioAlmacen::where('producto_id', $traslado->producto_id)
                ->where('almacen_id', $traslado->almacen_destino_id)
                ->first();
            
            $stockAnterior = $inventarioDestino ? $inventarioDestino->stock_actual : 0;
            $stockNuevo = $stockAnterior + $traslado->cantidad_recibida;
            
            if ($inventarioDestino) {
                $inventarioDestino->stock_actual = $stockNuevo;
                $inventarioDestino->save();
            } else {
                $inventarioDestino = InventarioAlmacen::create([
                    'producto_id' => $traslado->producto_id,
                    'almacen_id' => $traslado->almacen_destino_id,
                    'stock_actual' => $stockNuevo,
                    'stock_minimo' => 0,
                    'stock_maximo' => 999999,
                ]);
            }
            
            MovimientoInventario::create([
                'numero' => MovimientoInventario::generarNumero(),
                'producto_id' => $traslado->producto_id,
                'almacen_id' => $traslado->almacen_destino_id,
                'user_id' => auth()->id() ?? 1,
                'tipo' => 'traslado_entrada',
                'cantidad' => $traslado->cantidad_recibida,
                'stock_anterior' => $stockAnterior,
                'stock_nuevo' => $stockNuevo,
                'referencia_type' => Traslado::class,
                'referencia_id' => $traslado->id,
                'fecha_movimiento' => now(),
                'motivo' => "Recepcion de traslado #{$traslado->numero}",
            ]);
        }
    }
}