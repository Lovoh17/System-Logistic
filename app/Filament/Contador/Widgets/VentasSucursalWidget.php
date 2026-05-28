<?php

namespace App\Filament\Contador\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class VentasSucursalWidget extends ChartWidget
{
    protected static ?string $heading = 'Ventas por Sucursal (Año Actual)';
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 1;
    protected static ?string $pollingInterval = null;

    protected function getData(): array
    {
        $sucursales = DB::table('pedidos_venta as pv')
            ->join('almacenes as a', 'a.id', '=', 'pv.almacen_id')
            ->whereYear('pv.fecha_pedido', Carbon::now()->year)
            ->whereNotIn('pv.estado', ['cancelado', 'borrador'])
            ->whereNull('pv.deleted_at')
            ->select('a.nombre', DB::raw('SUM(pv.total) as total_ventas'), DB::raw('COUNT(pv.id) as num_pedidos'))
            ->groupBy('a.id', 'a.nombre')
            ->orderByDesc('total_ventas')
            ->get();

        $coloresFondo = [
            'rgba(5, 150, 105, 0.85)',
            'rgba(16, 185, 129, 0.85)',
            'rgba(245, 158, 11, 0.85)',
            'rgba(239, 68, 68, 0.85)',
            'rgba(99, 102, 241, 0.85)',
            'rgba(6, 182, 212, 0.85)',
        ];

        return [
            'datasets' => [
                [
                    'data'            => $sucursales->pluck('total_ventas')->map(fn($v) => round((float) $v, 2))->toArray(),
                    'backgroundColor' => array_slice($coloresFondo, 0, $sucursales->count()),
                    'borderColor'     => '#fff',
                    'borderWidth'     => 2,
                ],
            ],
            'labels' => $sucursales->pluck('nombre')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['position' => 'bottom'],
                'tooltip' => [
                    'callbacks' => [
                        'label' => "function(ctx){ return ctx.label + ': $' + ctx.parsed.toLocaleString('es-SV',{minimumFractionDigits:2}); }",
                    ],
                ],
            ],
            'cutout' => '60%',
        ];
    }
}