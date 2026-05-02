<?php

namespace App\Filament\Widgets;

use App\Models\PedidoVenta;
use App\Models\PedidoCompra;
use App\Models\Envio;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\Proveedor;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EstadisticasWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $ventasMes    = PedidoVenta::whereMonth('created_at', now()->month)->sum('total');
        $pedidosMes   = PedidoVenta::whereMonth('created_at', now()->month)->count();
        $enviosActivos = Envio::whereIn('estado', ['en_transito', 'despachado', 'en_destino'])->count();
        $stockCritico  = Producto::whereColumn('stock_actual', '<=', 'stock_minimo')->count();
        $entregasHoy   = Envio::whereDate('fecha_entrega_real', today())->where('estado', 'entregado')->count();
        $pedidosPend   = PedidoVenta::whereIn('estado', ['confirmado', 'en_preparacion'])->count();

        return [
            Stat::make('Ventas del Mes', '$' . number_format($ventasMes, 2))
                ->description("$pedidosMes pedidos este mes")
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('success')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3, (int)($ventasMes / 100)]),

            Stat::make('Envíos en Ruta', $enviosActivos)
                ->description('Actualmente en tránsito')
                ->descriptionIcon('heroicon-m-truck')
                ->color('info'),

            Stat::make('Entregas Hoy', $entregasHoy)
                ->description('Completadas satisfactoriamente')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('primary'),

            Stat::make('Pedidos Pendientes', $pedidosPend)
                ->description('Requieren atención')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pedidosPend > 10 ? 'danger' : 'warning'),

            Stat::make('Productos Stock Crítico', $stockCritico)
                ->description('En o por debajo del mínimo')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($stockCritico > 0 ? 'danger' : 'success'),

            Stat::make('Proveedores Activos', Proveedor::where('estado', 'activo')->count())
                ->description('De ' . Proveedor::count() . ' en total')
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('gray'),
        ];
    }
}
