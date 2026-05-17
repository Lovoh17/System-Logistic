<?php

namespace App\Filament\Pages\Logistica;

use App\Models\PedidoCompra;
use App\Models\PedidoVenta;
use App\Models\Envio;
use App\Models\Transportista;
use Filament\Pages\Page;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardLogistica extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?string $title = 'Panel de Logística';
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament.logistica.pages.dashboard-logistica';

    public function getStats(): array
    {
        return [
            Stat::make('Pedidos de Compra Pendientes', PedidoCompra::whereIn('estado', ['enviado', 'confirmado'])->count())
                ->icon('heroicon-o-shopping-bag')
                ->color('warning')
                ->url('/logistica/pedido-compras'),
            Stat::make('Pedidos de Venta por Despachar', PedidoVenta::whereIn('estado', ['confirmado', 'listo'])->count())
                ->icon('heroicon-o-shopping-cart')
                ->color('info')
                ->url('/logistica/pedido-ventas'),
            Stat::make('Envíos en Tránsito', Envio::whereIn('estado', ['despachado', 'en_transito'])->count())
                ->icon('heroicon-o-truck')
                ->color('warning')
                ->url('/logistica/envios'),
            Stat::make('Transportistas Disponibles', Transportista::where('estado', 'disponible')->count())
                ->icon('heroicon-o-truck')
                ->color('success')
                ->url('/logistica/transportistas'),
        ];
    }

    public function getPedidosCompraRecientes()
    {
        return PedidoCompra::with('proveedor')
            ->whereIn('estado', ['enviado', 'confirmado'])
            ->orderBy('fecha_requerida', 'asc')
            ->limit(5)
            ->get();
    }

    public function getEnviosRecientes()
    {
        return Envio::with(['pedidoVenta.cliente', 'transportista'])
            ->whereIn('estado', ['despachado', 'en_transito'])
            ->orderBy('fecha_entrega_estimada', 'asc')
            ->limit(5)
            ->get();
    }
}