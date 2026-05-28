<?php

namespace App\Filament\Contador\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TopProductosWidget extends ChartWidget
{
    protected static ?string $heading = 'Top 10 Productos Más Vendidos';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 1;
    protected static ?string $pollingInterval = null;

    protected function getData(): array
    {
        $top = DB::table('pedidos_venta_items as pvi')
            ->join('productos as p', 'p.id', '=', 'pvi.producto_id')
            ->join('pedidos_venta as pv', 'pv.id', '=', 'pvi.pedido_venta_id')
            ->whereNotIn('pv.estado', ['cancelado', 'borrador'])
            ->whereNull('pv.deleted_at')
            ->select('p.nombre', DB::raw('SUM(pvi.cantidad) as total_vendido'), DB::raw('SUM(pvi.subtotal) as total_ingresos'))
            ->groupBy('p.id', 'p.nombre')
            ->orderByDesc('total_vendido')
            ->limit(10)
            ->get();

        $colores = [
            'rgba(5, 150, 105, 0.8)',
            'rgba(16, 185, 129, 0.8)',
            'rgba(52, 211, 153, 0.8)',
            'rgba(110, 231, 183, 0.8)',
            'rgba(167, 243, 208, 0.8)',
            'rgba(245, 158, 11, 0.8)',
            'rgba(251, 191, 36, 0.8)',
            'rgba(253, 224, 71, 0.8)',
            'rgba(20, 184, 166, 0.8)',
            'rgba(6, 182, 212, 0.8)',
        ];

        return [
            'datasets' => [
                [
                    'label'           => 'Unidades Vendidas',
                    'data'            => $top->pluck('total_vendido')->toArray(),
                    'backgroundColor' => array_slice($colores, 0, $top->count()),
                    'borderColor'     => 'transparent',
                    'borderWidth'     => 1,
                ],
            ],
            'labels' => $top->map(fn($p) => mb_strimwidth($p->nombre, 0, 22, '...'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'plugins'   => [
                'legend' => ['display' => false],
            ],
            'scales' => [
                'x' => ['beginAtZero' => true, 'title' => ['display' => true, 'text' => 'Unidades']],
            ],
        ];
    }
}