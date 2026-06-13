<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Filament\Pages\Logistica\MapaTransportistas;
use App\Filament\Pages\Logistica\RedistribucionSucursales;
use App\Filament\Pages\RecomendacionesCompra;
use App\Filament\Resources\AlmacenResource;
use App\Filament\Resources\ClienteResource;
use App\Filament\Resources\EnvioResource;
use App\Filament\Resources\InventarioAlmacenResource;
use App\Filament\Resources\MovimientoInventarioResource;
use App\Filament\Resources\PedidoCompraResource;
use App\Filament\Resources\PedidoVentaResource;
use App\Filament\Resources\ProductoResource;
use App\Filament\Resources\ProveedorResource;
use App\Filament\Resources\TransportistaResource;
use App\Filament\Resources\TrasladoResource;
use App\Filament\Resources\UserResource;
use App\Filament\Widgets\AlertasInventario;
use App\Filament\Widgets\EnviosActivosWidget;
use App\Filament\Widgets\EstadisticasWidget;
use App\Filament\Widgets\GraficoVentasWidget;
use App\Filament\Widgets\PedidosPendientesWidget;
use App\Filament\Widgets\StockCriticoWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->profile()
            ->colors([
                'primary' => Color::Blue,
                'gray' => Color::Slate,
                'info' => Color::Sky,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
                'danger' => Color::Rose,
            ])
            ->font('Inter')
            ->brandName('AgroAlvarado')
            ->brandLogo(asset('images/logo.png'))
            ->brandLogoHeight('3.5rem')
            ->darkMode(false)
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups([
                NavigationGroup::make('Gestión de Socios')->icon('heroicon-o-users'),
                NavigationGroup::make('Inventario')->icon('heroicon-o-archive-box'),
                NavigationGroup::make('Pedidos')->icon('heroicon-o-document-text'),
                NavigationGroup::make('Logística')->icon('heroicon-o-truck'),
                NavigationGroup::make('Administración')->icon('heroicon-o-cog-6-tooth')->collapsed(),
            ])
            ->pages([
                Dashboard::class,
                MapaTransportistas::class,
                RedistribucionSucursales::class,
                RecomendacionesCompra::class,
            ])
            ->resources([
                AlmacenResource::class,
                ProveedorResource::class,
                ClienteResource::class,
                ProductoResource::class,
                MovimientoInventarioResource::class,
                PedidoCompraResource::class,
                UserResource::class,
                PedidoVentaResource::class,
                TransportistaResource::class,
                EnvioResource::class,
                InventarioAlmacenResource::class,
                TrasladoResource::class,
            ])
            ->widgets([
                EstadisticasWidget::class,
                GraficoVentasWidget::class,
                PedidosPendientesWidget::class,
                StockCriticoWidget::class,
                EnviosActivosWidget::class,
                AlertasInventario::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([Authenticate::class])
            ->globalSearch()
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->breadcrumbs(true)
            ->maxContentWidth('full');
    }
}
