<?php

namespace App\Filament\Contador\Widgets;

use App\Models\Cliente;
use App\Models\PedidoCompra;
use App\Models\PedidoVenta;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class KPIsFinancierosWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $mesActual  = now()->month;
        $anioActual = now()->year;

        $ventasMes = PedidoVenta::whereYear('fecha_pedido', $anioActual)
            ->whereMonth('fecha_pedido', $mesActual)
            ->whereNotIn('estado', ['cancelado', 'borrador'])
            ->sum('total');

        $comprasMes = PedidoCompra::whereYear('fecha_pedido', $anioActual)
            ->whereMonth('fecha_pedido', $mesActual)
            ->whereNotIn('estado', ['cancelado', 'borrador'])
            ->sum('total');

        $utilidadBruta = $ventasMes - $comprasMes;

        $pedidosMes = PedidoVenta::whereYear('fecha_pedido', $anioActual)
            ->whereMonth('fecha_pedido', $mesActual)
            ->whereNotIn('estado', ['cancelado', 'borrador'])
            ->count();

        $clientesCreditoPendiente = Cliente::where('limite_credito', '>', 0)
            ->whereHas('pedidosVenta', function ($q) {
                $q->whereNotIn('estado', ['entregado', 'cancelado', 'borrador']);
            })
            ->count();

        $mesPasado = now()->subMonth();
        $ventasMesPasado = PedidoVenta::whereYear('fecha_pedido', $mesPasado->year)
            ->whereMonth('fecha_pedido', $mesPasado->month)
            ->whereNotIn('estado', ['cancelado', 'borrador'])
            ->sum('total');

        $tendenciaVentas = $ventasMesPasado > 0
            ? round((($ventasMes - $ventasMesPasado) / $ventasMesPasado) * 100, 1)
            : 0;

        $tendenciaIcono = $tendenciaVentas >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
        $tendenciaColor = $tendenciaVentas >= 0 ? 'success' : 'danger';

        return [
            Stat::make('Ventas del Mes', '$' . number_format($ventasMes, 2))
                ->description($tendenciaVentas >= 0 ? "+{$tendenciaVentas}% vs mes anterior" : "{$tendenciaVentas}% vs mes anterior")
                ->descriptionIcon($tendenciaIcono)
                ->color($tendenciaColor)
                ->chart($this->getMiniChartVentas()),

            Stat::make('Compras del Mes', '$' . number_format($comprasMes, 2))
                ->description("$pedidosMes pedidos de venta")
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('warning'),

            Stat::make('Utilidad Bruta', '$' . number_format($utilidadBruta, 2))
                ->description($utilidadBruta >= 0 ? 'Balance positivo' : 'Balance negativo')
                ->descriptionIcon($utilidadBruta >= 0 ? 'heroicon-m-check-circle' : 'heroicon-m-exclamation-triangle')
                ->color($utilidadBruta >= 0 ? 'success' : 'danger'),

            Stat::make('Pedidos del Mes', $pedidosMes)
                ->description('Pedidos activos este mes')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),

            Stat::make('Crédito Pendiente', $clientesCreditoPendiente . ' clientes')
                ->description('Con pedidos sin liquidar')
                ->descriptionIcon('heroicon-m-clock')
                ->color($clientesCreditoPendiente > 0 ? 'warning' : 'success'),

            Stat::make('Margen Bruto', $ventasMes > 0 ? number_format((($utilidadBruta / $ventasMes) * 100), 1) . '%' : '0%')
                ->description('Utilidad / Ventas')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($utilidadBruta >= 0 ? 'success' : 'danger'),
        ];
    }

    private function getMiniChartVentas(): array
    {
        $datos = [];
        for ($i = 5; $i >= 0; $i--) {
            $mes = now()->subMonths($i);
            $datos[] = (float) PedidoVenta::whereYear('fecha_pedido', $mes->year)
                ->whereMonth('fecha_pedido', $mes->month)
                ->whereNotIn('estado', ['cancelado', 'borrador'])
                ->sum('total');
        }
        return $datos;
    }
}