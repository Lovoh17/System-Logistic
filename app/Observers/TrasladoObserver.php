<?php

namespace App\Observers;

use App\Models\Traslado;
use App\Services\InventoryService;
use Illuminate\Support\Facades\DB;

/**
 * Mueve inventario de un traslado en dos pasos (basado en transiciones de estado),
 * delegando toda mutación de stock + kardex a InventoryService.
 *
 *   1. → en_transito : descuenta stock en la sucursal ORIGEN (traslado_salida)
 *   2. → completado  : incrementa stock en la sucursal DESTINO (traslado_entrada)
 *
 * Cancelación: revierte únicamente lo ya movido, según el estado previo:
 *   - desde en_transito : devuelve al origen
 *   - desde completado  : devuelve al origen y retira del destino
 *
 * La salida del origen usa cantidad_sugerida; la entrada al destino usa
 * cantidad_real (lo realmente recibido) con fallback a cantidad_sugerida.
 */
class TrasladoObserver
{
    public function __construct(private InventoryService $inventory) {}

    public function updated(Traslado $traslado): void
    {
        if (! $traslado->wasChanged('estado')) {
            return;
        }

        $nuevo = $traslado->estado;
        $anterior = $traslado->getOriginal('estado');

        match ($nuevo) {
            'en_transito' => $this->salidaOrigen($traslado),
            'completado' => $this->entradaDestino($traslado),
            'cancelado' => $this->revertir($traslado, $anterior),
            default => null,
        };
    }

    /** Paso 1: descuenta del origen lo enviado (cantidad_sugerida). */
    private function salidaOrigen(Traslado $traslado): void
    {
        DB::transaction(function () use ($traslado) {
            foreach ($traslado->items()->get() as $item) {
                $cantidad = (float) $item->cantidad_sugerida;
                if ($cantidad <= 0) {
                    continue;
                }

                $this->inventory->salida(
                    $item->producto_id,
                    $traslado->almacen_origen_id,
                    $cantidad,
                    'traslado_salida',
                    "Salida por traslado {$traslado->numero} hacia sucursal #{$traslado->almacen_destino_id}",
                    $traslado,
                    userId: auth()->id() ?? $traslado->creado_por,
                );
            }
        });
    }

    /** Paso 2: ingresa al destino lo recibido (cantidad_real, fallback sugerida). */
    private function entradaDestino(Traslado $traslado): void
    {
        DB::transaction(function () use ($traslado) {
            foreach ($traslado->items()->get() as $item) {
                $cantidad = (float) ($item->cantidad_real ?? $item->cantidad_sugerida);
                if ($cantidad <= 0) {
                    continue;
                }

                $this->inventory->entrada(
                    $item->producto_id,
                    $traslado->almacen_destino_id,
                    $cantidad,
                    'traslado_entrada',
                    "Entrada por traslado {$traslado->numero} desde sucursal #{$traslado->almacen_origen_id}",
                    $traslado,
                    userId: auth()->id() ?? $traslado->creado_por,
                );
            }
        });
    }

    /** Revierte solo el movimiento ya aplicado según el estado previo. */
    private function revertir(Traslado $traslado, ?string $anterior): void
    {
        // Si nunca salió del origen, no hay nada que revertir.
        if (! in_array($anterior, ['en_transito', 'completado'], true)) {
            return;
        }

        DB::transaction(function () use ($traslado, $anterior) {
            $userId = auth()->id() ?? $traslado->creado_por;

            foreach ($traslado->items()->get() as $item) {
                // Si ya había entrado al destino (completado), retirarlo del destino.
                if ($anterior === 'completado') {
                    $cantDestino = (float) ($item->cantidad_real ?? $item->cantidad_sugerida);
                    if ($cantDestino > 0) {
                        $this->inventory->salida(
                            $item->producto_id,
                            $traslado->almacen_destino_id,
                            $cantDestino,
                            'traslado_salida',
                            "Reverso de traslado {$traslado->numero}: retiro del destino por cancelación",
                            $traslado,
                            userId: $userId,
                        );
                    }
                }

                // En ambos casos ya había salido del origen: devolverlo.
                $cantOrigen = (float) $item->cantidad_sugerida;
                if ($cantOrigen > 0) {
                    $this->inventory->entrada(
                        $item->producto_id,
                        $traslado->almacen_origen_id,
                        $cantOrigen,
                        'traslado_entrada',
                        "Reverso de traslado {$traslado->numero}: devolución al origen por cancelación",
                        $traslado,
                        userId: $userId,
                    );
                }
            }
        });
    }
}
