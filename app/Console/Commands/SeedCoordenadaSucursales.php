<?php

namespace App\Console\Commands;

use App\Models\Almacen;
use App\Services\DistanceCalculator;
use Illuminate\Console\Command;

/**
 * Asigna coordenadas de ejemplo a sucursales de la zona oriente de El Salvador
 * y precalcula la tabla distancias_sucursales con Haversine.
 *
 * Uso: php artisan sucursales:seed-coordenadas
 */
class SeedCoordenadaSucursales extends Command
{
    protected $signature   = 'sucursales:seed-coordenadas {--recalcular : Solo recalcula distancias sin sobrescribir coordenadas}';
    protected $description = 'Asigna coordenadas geográficas a sucursales y calcula distancias Haversine.';

    // Ciudades de referencia — zona oriente El Salvador
    private array $coordenadasReferencia = [
        'San Miguel'         => ['lat' => 13.4833, 'lon' => -88.1833],
        'Usulután'           => ['lat' => 13.3494, 'lon' => -88.4447],
        'La Unión'           => ['lat' => 13.3344, 'lon' => -87.8436],
        'San Francisco Gotera' => ['lat' => 13.7003, 'lon' => -88.1003],
        'Santa Rosa de Lima' => ['lat' => 13.6233, 'lon' => -87.8772],
        'Santiago de María'  => ['lat' => 13.4836, 'lon' => -88.4694],
        'Sensuntepeque'      => ['lat' => 13.8775, 'lon' => -88.6286],
        'Cojutepeque'        => ['lat' => 13.7167, 'lon' => -88.9333],
    ];

    public function handle(): int
    {
        $soloRecalcular = $this->option('recalcular');

        if (!$soloRecalcular) {
            $this->asignarCoordenadas();
        }

        $this->recalcularDistancias();

        return self::SUCCESS;
    }

    private function asignarCoordenadas(): void
    {
        $almacenes = Almacen::all();
        $asignados = 0;

        foreach ($almacenes as $almacen) {
            // Buscar coincidencia por nombre (parcial, insensible a mayúsculas)
            foreach ($this->coordenadasReferencia as $ciudad => $coords) {
                if (stripos($almacen->nombre, $ciudad) !== false ||
                    stripos($ciudad, $almacen->nombre) !== false) {
                    $almacen->update(['latitud' => $coords['lat'], 'longitud' => $coords['lon']]);
                    $this->line("  ✓ {$almacen->nombre} → lat: {$coords['lat']}, lon: {$coords['lon']}");
                    $asignados++;
                    break;
                }
            }

            if (!$almacen->latitud) {
                $this->warn("  ? {$almacen->nombre} — sin coordenadas de referencia. Asígnelas manualmente.");
            }
        }

        $this->info("Coordenadas asignadas: {$asignados} sucursal(es).");
    }

    private function recalcularDistancias(): void
    {
        $pares = app(DistanceCalculator::class)->recalcularTodas();
        $this->info("Distancias calculadas: {$pares} par(es) entre sucursales con coordenadas.");
    }
}
