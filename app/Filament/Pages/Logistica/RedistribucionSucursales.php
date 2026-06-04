<?php

namespace App\Filament\Pages\Logistica;

use App\Models\Traslado;
use App\Models\TrasladoItem;
use App\Services\DistanceCalculator;
use App\Services\RedistribucionService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class RedistribucionSucursales extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-arrows-right-left';
    protected static ?string $navigationLabel = 'Redistribución de Stock';
    protected static ?string $title           = 'Redistribución Inteligente entre Sucursales';
    protected static ?int    $navigationSort  = 3;
    protected static ?string $navigationGroup = 'Logística';
    protected static string  $view            = 'filament.pages.redistribucion-sucursales';

    public array $sugerencias     = [];
    public array $almacenesMatriz = [];
    public array $matrizKm        = [];
    public bool  $mostrarMatriz   = false;
    public bool  $calculando      = false;

    public function mount(): void
    {
        $this->cargarDatos();
    }

    protected function cargarDatos(): void
    {
        $service              = app(RedistribucionService::class);
        $this->sugerencias    = $service->analizarDesequilibrios();
        $matrizData           = $service->matrizDistancias();
        $this->almacenesMatriz = $matrizData['almacenes']->toArray();
        $this->matrizKm       = array_map(fn($row) => array_map(fn($v) => $v, $row), $matrizData['matriz']);
    }

    public function recalcular(): void
    {
        $pares = app(DistanceCalculator::class)->recalcularTodas();
        $this->cargarDatos();

        Notification::make()
            ->success()
            ->title('Análisis actualizado')
            ->body("Se recalcularon {$pares} distancias entre sucursales.")
            ->send();
    }

    public function toggleMatriz(): void
    {
        $this->mostrarMatriz = !$this->mostrarMatriz;
    }

    public function crearTraslado(int $index): void
    {
        $sug = $this->sugerencias[$index] ?? null;

        if ($sug === null) {
            Notification::make()->danger()->title('Sugerencia no encontrada')->send();
            return;
        }

        try {
            $traslado = Traslado::create([
                'numero'             => Traslado::generarNumero(),
                'almacen_origen_id'  => $sug['origen_id'],
                'almacen_destino_id' => $sug['destino_id'],
                'estado'             => 'sugerido',
                'motivo'             => "Redistribución automática: \"{$sug['producto_nombre']}\" "
                    . "— déficit en {$sug['destino_nombre']} "
                    . "({$sug['distancia_km']} km desde {$sug['origen_nombre']}). "
                    . "Costo estimado: \${$sug['costo_estimado']}.",
                'creado_por'         => auth()->id(),
            ]);

            TrasladoItem::create([
                'traslado_id'       => $traslado->id,
                'producto_id'       => $sug['producto_id'],
                'cantidad_sugerida' => $sug['cantidad_sugerida'],
                'notas'             => "Dist: {$sug['distancia_km']} km · "
                    . "Stock origen: {$sug['stock_origen']} · "
                    . "Excedente: {$sug['excedente']} · "
                    . "Déficit destino: {$sug['deficit']} · "
                    . "Costo est.: \${$sug['costo_estimado']}.",
            ]);

            Notification::make()
                ->success()
                ->title("Traslado {$traslado->numero} creado")
                ->body(
                    "{$sug['origen_nombre']} → {$sug['destino_nombre']}" . PHP_EOL .
                    "Producto: {$sug['producto_nombre']}" . PHP_EOL .
                    "Cantidad: {$sug['cantidad_sugerida']} u. · {$sug['distancia_km']} km · \${$sug['costo_estimado']}"
                )
                ->send();

            $this->cargarDatos();

        } catch (\Throwable $e) {
            Notification::make()
                ->danger()
                ->title('Error al crear traslado')
                ->body($e->getMessage())
                ->send();
        }
    }

    public function urgenciaLabel(int $nivel): string
    {
        return match ($nivel) {
            3       => 'Sin stock',
            2       => 'Crítico',
            default => 'Normal',
        };
    }

    public function urgenciaClase(int $nivel): string
    {
        return match ($nivel) {
            3       => 'bg-danger-100 dark:bg-danger-900/30 text-danger-700 dark:text-danger-400',
            2       => 'bg-warning-100 dark:bg-warning-900/30 text-warning-700 dark:text-warning-400',
            default => 'bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400',
        };
    }

    public function distanciaClase(float $km): string
    {
        if ($km <= 10) return 'text-success-600 dark:text-success-400 font-semibold';
        if ($km <= 30) return 'text-warning-600 dark:text-warning-400';
        return 'text-danger-600 dark:text-danger-400';
    }
}