<?php

namespace App\Filament\Contador\Widgets;

use App\Models\PedidoCompra;
use App\Models\PedidoVenta;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class GraficoVentasMensualesWidget extends ChartWidget
{
    protected static ?string $heading = 'Ventas vs Compras — Últimos 6 Meses';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $pollingInterval = null;

    protected function getData(): array
    {
        $meses   = [];
        $ventas  = [];
        $compras = [];

        for ($i = 5; $i >= 0; $i--) {
            $mes     = Carbon::now()->subMonths($i);
            $meses[] = $mes->translatedFormat('M Y');

            $ventas[] = (float) PedidoVenta::whereYear('fecha_pedido', $mes->year)
                ->whereMonth('fecha_pedido', $mes->month)
                ->whereNotIn('estado', ['cancelado', 'borrador'])
                ->sum('total');

            $compras[] = (float) PedidoCompra::whereYear('fecha_pedido', $mes->year)
                ->whereMonth('fecha_pedido', $mes->month)
                ->whereNotIn('estado', ['cancelado', 'borrador'])
                ->sum('total');
        }

        return [
            'datasets' => [
                [
                    'label'           => 'Ventas ($)',
                    'data'            => $ventas,
                    'borderColor'     => '#059669',
                    'backgroundColor' => 'rgba(5, 150, 105, 0.15)',
                    'fill'            => true,
                    'tension'         => 0.4,
                    'borderWidth'     => 2,
                ],
                [
                    'label'           => 'Compras ($)',
                    'data'            => $compras,
                    'borderColor'     => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.15)',
                    'fill'            => true,
                    'tension'         => 0.4,
                    'borderWidth'     => 2,
                ],
            ],
            'labels' => $meses,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['position' => 'top'],
                'tooltip' => [
                    'callbacks' => [
                        'label' => "function(ctx){ return ctx.dataset.label + ': $' + ctx.parsed.y.toLocaleString('es-SV',{minimumFractionDigits:2}); }",
                    ],
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => "function(v){ return '$' + v.toLocaleString('es-SV'); }",
                    ],
                ],
            ],
        ];
    }
}