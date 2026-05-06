<?php

namespace App\Providers\Filament;

use Filament\Panel;
use Filament\PanelProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;

class VentasPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('ventas')
            ->path('ventas')
            ->login()
            ->colors([
                'primary' => '#10b981',
            ])
            ->brandName('TraceLog - Punto de Venta')
            ->brandLogo(asset('images/logo.png'))
            ->brandLogoHeight('2rem')
            ->resources([
                \App\Filament\Resources\ClienteResource::class,
                \App\Filament\Resources\PedidoVentaResource::class,
            ])
            ->widgets([
                \App\Filament\Widgets\PuntoVentaWidget::class,
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