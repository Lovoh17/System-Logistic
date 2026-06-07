<?php

namespace App\Providers\Filament;

use App\Filament\Sucursal\Pages\DashboardSucursal;
use App\Filament\Sucursal\Pages\ReportesSucursal;
use App\Filament\Sucursal\Resources\InventarioSucursalResource;
use App\Filament\Sucursal\Resources\PedidoVentaSucursalResource;
use App\Filament\Sucursal\Resources\TrasladoSucursalResource;
use App\Filament\Sucursal\Resources\TransportistaSucursalResource;
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

class SucursalPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('sucursal')
            ->path('sucursal')
            ->login()
            ->colors([
                'primary' => Color::Teal,
                'gray'    => Color::Slate,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
                'danger'  => Color::Rose,
                'info'    => Color::Sky,
            ])
            ->font('Inter')
            ->brandName('AgroAlvarado — Mi Sucursal')
            ->brandLogo(asset('images/logo.png'))
            ->brandLogoHeight('2.5rem')
            ->darkMode(false)
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups([
                NavigationGroup::make('Mi Sucursal')->icon('heroicon-o-building-storefront'),
                NavigationGroup::make('Ventas')->icon('heroicon-o-shopping-cart'),
                NavigationGroup::make('Traslados')->icon('heroicon-o-arrow-path'),
                NavigationGroup::make('Reportes')->icon('heroicon-o-chart-bar'),
            ])
            ->pages([
                DashboardSucursal::class,
                ReportesSucursal::class,
            ])
            ->resources([
                InventarioSucursalResource::class,
                PedidoVentaSucursalResource::class,
                TrasladoSucursalResource::class,
                TransportistaSucursalResource::class,
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
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->breadcrumbs(true)
            ->maxContentWidth('full');
    }
}
