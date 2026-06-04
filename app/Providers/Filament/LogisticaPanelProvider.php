<?php

namespace App\Providers\Filament;

use Filament\Panel;
use Filament\PanelProvider;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;

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
                \App\Filament\Pages\Logistica\DashboardLogistica::class,
                \App\Filament\Pages\Logistica\RedistribucionSucursales::class,
            ])
            ->resources([
                \App\Filament\Resources\PedidoCompraResource::class,
                \App\Filament\Resources\PedidoVentaResource::class,
                \App\Filament\Resources\TransportistaResource::class,
                \App\Filament\Resources\EnvioResource::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}