<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\EstadisticasWidget;
use App\Filament\Widgets\PedidosPendientesWidget;
use App\Filament\Widgets\StockCriticoWidget;
use App\Filament\Widgets\EnviosActivosWidget;
use App\Filament\Widgets\GraficoVentasWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';
    protected static ?string $title = 'Panel de Control Logístico';
    protected static ?string $navigationLabel = 'Dashboard';

    public function getWidgets(): array
    {
        return [
            EstadisticasWidget::class,
            GraficoVentasWidget::class,
            PedidosPendientesWidget::class,
            StockCriticoWidget::class,
            EnviosActivosWidget::class,
        ];
    }

    public function getColumns(): int | string | array
    {
        return 2;
    }
}
