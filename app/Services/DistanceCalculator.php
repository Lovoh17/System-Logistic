<?php

namespace App\Services;

use App\Models\Almacen;
use App\Models\DistanciaSucursal;

/**
 * Calcula distancias entre sucursales usando la fórmula de Haversine
 * y persiste los resultados en la tabla distancias_sucursales como caché.
 *
 * La tabla almacena siempre con almacen1_id < almacen2_id para
 * evitar duplicados, ya que la distancia es simétrica: dist(A,B) = dist(B,A).
 */
class DistanceCalculator
{
    private const EARTH_RADIUS_KM = 6371.0;

    /**
     * Haversine puro: distancia en km entre dos pares de coordenadas.
     */
    public function calculate(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $lat1r = deg2rad($lat1);
        $lat2r = deg2rad($lat2);
        $dLat  = deg2rad($lat2 - $lat1);
        $dLon  = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) ** 2
           + cos($lat1r) * cos($lat2r) * sin($dLon / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round(self::EARTH_RADIUS_KM * $c, 2);
    }

    /**
     * Distancia entre dos Almacen con caché en BD.
     * Devuelve null si alguno no tiene coordenadas configuradas.
     */
    public function betweenAlmacenes(Almacen $a, Almacen $b): ?float
    {
        if ($a->id === $b->id) return 0.0;

        // Normalizar IDs: el menor siempre va primero
        [$id1, $id2] = $a->id < $b->id ? [$a->id, $b->id] : [$b->id, $a->id];

        $cached = DistanciaSucursal::where('almacen1_id', $id1)
            ->where('almacen2_id', $id2)
            ->first();

        if ($cached) return (float) $cached->distancia_km;

        if (!$a->tieneCoordenadas() || !$b->tieneCoordenadas()) return null;

        $distancia = $this->calculate(
            (float) $a->latitud, (float) $a->longitud,
            (float) $b->latitud, (float) $b->longitud
        );

        DistanciaSucursal::create([
            'almacen1_id'  => $id1,
            'almacen2_id'  => $id2,
            'distancia_km' => $distancia,
        ]);

        return $distancia;
    }

    /**
     * Recalcula y persiste las distancias entre TODAS las sucursales activas
     * con coordenadas. Sobrescribe el caché existente.
     * Devuelve el número de pares calculados.
     */
    public function recalcularTodas(): int
    {
        $almacenes = Almacen::activo()
            ->whereNotNull('latitud')
            ->whereNotNull('longitud')
            ->get();

        $count = 0;

        for ($i = 0; $i < $almacenes->count(); $i++) {
            for ($j = $i + 1; $j < $almacenes->count(); $j++) {
                $a = $almacenes[$i];
                $b = $almacenes[$j];

                $distancia = $this->calculate(
                    (float) $a->latitud, (float) $a->longitud,
                    (float) $b->latitud, (float) $b->longitud
                );

                DistanciaSucursal::updateOrCreate(
                    ['almacen1_id' => $a->id, 'almacen2_id' => $b->id],
                    ['distancia_km' => $distancia]
                );

                $count++;
            }
        }

        return $count;
    }
}
