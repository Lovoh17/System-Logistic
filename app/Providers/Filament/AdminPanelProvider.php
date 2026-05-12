<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Filament\Pages\MapaTransportistas;
use App\Filament\Resources\{ClienteResource, TrasladoResource ,InventarioAlmacenResource, UserResource , EnvioResource, MovimientoInventarioResource};
use App\Filament\Resources\{PedidoCompraResource, PedidoVentaResource, ProductoResource};
use App\Filament\Resources\{ProveedorResource, TransportistaResource};
use App\Filament\Widgets\{EnviosActivosWidget, EstadisticasWidget, GraficoVentasWidget};
use App\Filament\Widgets\{PedidosPendientesWidget, StockCriticoWidget, AlertasInventario};
use Filament\Http\Middleware\{Authenticate, DisableBladeIconComponents, DispatchServingFilamentEvent};
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\{AddQueuedCookiesToResponse, EncryptCookies};
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\{AuthenticateSession, StartSession};
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
                'gray'    => Color::Slate,
                'info'    => Color::Sky,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
                'danger'  => Color::Rose,
            ])
            ->font('Inter')
            ->brandName('AgroAlvarado')
            ->brandLogo(asset('images/logo.png')) 
            ->brandLogoHeight('3.5rem')
            ->darkMode(true)
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
            ])
            ->resources([
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