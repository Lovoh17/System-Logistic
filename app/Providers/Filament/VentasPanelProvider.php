<?php

namespace App\Providers\Filament;

use App\Filament\Pages\PuntoVenta;
use App\Filament\Pages\InventarioSucursal;
use App\Filament\Ventas\Pages\DashboardVentas;
use App\Models\User;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;   // ← sin espacio
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Filament\Resources\{InventarioAlmacenResource,ProductoResource };

class VentasPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('ventas')
            ->path('ventas')
            ->login()

            ->homeUrl(fn (): string => User::getHomeUrl())

            ->colors([
                'primary' => '#10b981',
                'gray'    => '#6b7280',
            ])
            ->brandName('TraceLog - Punto de Venta')
            ->brandLogo(asset('images/logo.png'))
            ->brandLogoHeight('2rem')
            ->darkMode(false)
            ->sidebarCollapsibleOnDesktop()

            ->navigationGroups([
                NavigationGroup::make('Caja')->icon('heroicon-o-shopping-cart'),
                NavigationGroup::make('Clientes')->icon('heroicon-o-users'),
                NavigationGroup::make('Historial')->icon('heroicon-o-document-text'),
            ])

            ->pages([
                PuntoVenta::class,       
                InventarioSucursal::class
            ])

            ->resources([
                \App\Filament\Resources\PedidoVentaResource::class,
                
                InventarioAlmacenResource::class,
                ProductoResource::class,
            ])

            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,          // ← corregido
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}