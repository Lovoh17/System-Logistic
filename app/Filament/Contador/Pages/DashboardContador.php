<?php

namespace App\Filament\Contador\Pages;

use App\Filament\Contador\Widgets\GraficoVentasMensualesWidget;
use App\Filament\Contador\Widgets\KPIsFinancierosWidget;
use App\Filament\Contador\Widgets\TopProductosWidget;
use App\Filament\Contador\Widgets\VentasSucursalWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class DashboardContador extends BaseDashboard
{
    protected static ?string $navigationIcon  = 'heroicon-o-chart-pie';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?string $title           = 'Panel Financiero';
    protected static ?string $navigationGroup = 'Finanzas';
    protected static ?int    $navigationSort  = 1;

    public function getWidgets(): array
    {
        return [
            KPIsFinancierosWidget::class,
            GraficoVentasMensualesWidget::class,
            TopProductosWidget::class,
            VentasSucursalWidget::class,
        ];
    }

    public function getColumns(): int | string | array
    {
        return 2;
    }
}