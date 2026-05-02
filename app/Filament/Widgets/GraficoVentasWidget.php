<?php

namespace App\Filament\Widgets;

use App\Models\PedidoVenta;
use App\Models\Envio;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class GraficoVentasWidget extends ChartWidget
{
    protected static ?string $heading = 'Ventas y Entregas - Últimos 6 Meses';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $meses  = [];
        $ventas = [];
        $entregas = [];

        for ($i = 5; $i >= 0; $i--) {
            $mes = Carbon::now()->subMonths($i);
            $meses[] = $mes->translatedFormat('M Y');

            $ventas[] = (float) PedidoVenta::whereYear('fecha_pedido', $mes->year)
                ->whereMonth('fecha_pedido', $mes->month)
                ->whereNotIn('estado', ['cancelado', 'borrador'])
                ->sum('total');

            $entregas[] = Envio::whereYear('created_at', $mes->year)
                ->whereMonth('created_at', $mes->month)
                ->where('estado', 'entregado')
                ->count() * 100; // Scale for visual comparison
        }

        return [
            'datasets' => [
                [
                    'label'           => 'Ventas ($)',
                    'data'            => $ventas,
                    'borderColor'     => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill'            => true,
                    'tension'         => 0.4,
                ],
                [
                    'label'           => 'Entregas (×$100)',
                    'data'            => $entregas,
                    'borderColor'     => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill'            => true,
                    'tension'         => 0.4,
                ],
            ],
            'labels' => $meses,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
