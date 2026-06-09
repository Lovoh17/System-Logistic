<?php

namespace App\Services;

use App\Models\Almacen;
use App\Models\InventarioAlmacen;
use App\Models\Transportista;

class RedistribucionService
{
    private const TARIFA_KM_DEFAULT = 0.50;

    public function __construct(private DistanceCalculator $distCalc) {}

    public function analizarDesequilibrios(): array
    {
        // Precarga transportistas disponibles indexados por almacen_id
        $transportistasDisponibles = Transportista::where('estado', 'disponible')
            ->with('user')
            ->get()
            ->groupBy('almacen_id');

        $inventarios = InventarioAlmacen::with(['producto', 'almacen'])
            ->whereHas('producto', fn($q) => $q->activo())
            ->get()
            ->groupBy('producto_id');

        $sugerencias = [];

        foreach ($inventarios as $productoId => $items) {
            if ($items->count() < 2) continue;

            $deficit = $items->filter(
                fn($inv) => $inv->stock_minimo > 0 && $inv->stock_actual < $inv->stock_minimo
            );
            $sobrestock = $items->filter(
                fn($inv) => $inv->stock_maximo > 0 && $inv->stock_actual > $inv->stock_maximo
            );

            if ($deficit->isEmpty() || $sobrestock->isEmpty()) continue;

            foreach ($deficit as $dest) {
                $mejorOrigen       = null;
                $mejorDistancia    = PHP_FLOAT_MAX;
                $mejorExcedente    = 0.0;
                $mejorTransportista = null;

                foreach ($sobrestock as $orig) {
                    if ($orig->almacen_id === $dest->almacen_id) continue;

                    $dist = $this->distCalc->betweenAlmacenes($orig->almacen, $dest->almacen);
                    if ($dist === null) continue;

                    // Preferir origen con transportista disponible; si empatan distancia, ese gana
                    $tieneTransportista = $transportistasDisponibles->has($orig->almacen_id);

                    $mejorTieneTransportista = $mejorOrigen
                        ? $transportistasDisponibles->has($mejorOrigen->almacen_id)
                        : false;

                    $esMejor = $dist < $mejorDistancia
                        || ($dist === $mejorDistancia && $tieneTransportista && !$mejorTieneTransportista);

                    if ($esMejor) {
                        $mejorDistancia    = $dist;
                        $mejorOrigen       = $orig;
                        $mejorExcedente    = (float) $orig->stock_actual - (float) $orig->stock_maximo;
                        $mejorTransportista = $transportistasDisponibles->get($orig->almacen_id)?->first();
                    }
                }

                if ($mejorOrigen === null) continue;

                $cantNecesaria  = (float) $dest->stock_minimo - (float) $dest->stock_actual;
                $cantTransferir = min($cantNecesaria, $mejorExcedente);

                if ($cantTransferir <= 0) continue;

                $sugerencias[] = [
                    'producto_id'          => $dest->producto_id,
                    'producto_nombre'      => $dest->producto?->nombre ?? '—',
                    'origen_id'            => $mejorOrigen->almacen_id,
                    'origen_nombre'        => $mejorOrigen->almacen?->nombre ?? '—',
                    'destino_id'           => $dest->almacen_id,
                    'destino_nombre'       => $dest->almacen?->nombre ?? '—',
                    'stock_origen'         => round((float) $mejorOrigen->stock_actual, 4),
                    'stock_maximo_origen'  => round((float) $mejorOrigen->stock_maximo, 4),
                    'stock_destino'        => round((float) $dest->stock_actual, 4),
                    'stock_minimo_destino' => round((float) $dest->stock_minimo, 4),
                    'excedente'            => round($mejorExcedente, 4),
                    'deficit'              => round($cantNecesaria, 4),
                    'cantidad_sugerida'    => round($cantTransferir, 4),
                    'distancia_km'         => round($mejorDistancia, 4),
                    'costo_estimado'       => round($mejorDistancia * self::TARIFA_KM_DEFAULT, 2),
                    'urgencia'             => $this->calcularUrgencia($dest),
                    // Transportista disponible en el almacén origen
                    'transportista_id'     => $mejorTransportista?->id,
                    'transportista_nombre' => $mejorTransportista?->user?->name ?? null,
                    'transportista_placa'  => $mejorTransportista?->vehiculo_placa ?? null,
                ];
            }
        }

        usort($sugerencias, function ($a, $b) {
            if ($b['urgencia'] !== $a['urgencia']) {
                return $b['urgencia'] <=> $a['urgencia'];
            }
            return $a['distancia_km'] <=> $b['distancia_km'];
        });

        return $sugerencias;
    }

    public function matrizDistancias(): array
    {
        $almacenes = Almacen::activo()
            ->whereNotNull('latitud')
            ->whereNotNull('longitud')
            ->get();

        $matriz = [];

        foreach ($almacenes as $a) {
            foreach ($almacenes as $b) {
                if ($a->id === $b->id) {
                    $matriz[$a->id][$b->id] = 0.0;
                    continue;
                }

                if (!isset($matriz[$a->id][$b->id])) {
                    $dist = $this->distCalc->betweenAlmacenes($a, $b);
                    $matriz[$a->id][$b->id] = $dist;
                    $matriz[$b->id][$a->id] = $dist;
                }
            }
        }

        return ['almacenes' => $almacenes->values(), 'matriz' => $matriz];
    }

    private function calcularUrgencia(InventarioAlmacen $inv): int
    {
        $stock = (float) $inv->stock_actual;
        $min   = (float) $inv->stock_minimo;

        if ($stock <= 0)                       return 3;
        if ($min > 0 && $stock < $min * 0.5)  return 2;
        return 1;
    }
}
