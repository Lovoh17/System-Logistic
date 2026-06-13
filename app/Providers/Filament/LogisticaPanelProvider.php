<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Logistica\DashboardLogistica;
use App\Filament\Pages\Logistica\RedistribucionSucursales;
use App\Filament\Pages\RecomendacionesCompra;
use App\Filament\Resources\EnvioResource;
use App\Filament\Resources\PedidoCompraResource;
use App\Filament\Resources\PedidoVentaResource;
use App\Filament\Resources\TransportistaResource;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class LogisticaPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('logistica')
            ->path('logistica')
            ->login()
            ->colors([
                'primary' => '#3b82f6',
            ])
            ->brandName('TraceLog - Logística')
            ->brandLogo(asset('images/logo.png'))
            ->brandLogoHeight('2.5rem')
            ->darkMode(false)
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups([
                'Pedidos',
                'Transporte',
                'Envíos',
                'Reportes',
            ])
            ->pages([
                DashboardLogistica::class,
                RedistribucionSucursales::class,
                RecomendacionesCompra::class,
            ])
            ->resources([
                PedidoCompraResource::class,
                PedidoVentaResource::class,
                TransportistaResource::class,
                EnvioResource::class,
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
            ->authMiddleware([
                Authenticate::class,
            ])
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s');
    }
}
