<?php

namespace App\Providers\Filament;

use App\Filament\Contador\Pages\BalanceGeneral;
use App\Filament\Contador\Pages\CuentasPorCobrar;
use App\Filament\Contador\Pages\CuentasPorPagar;
use App\Filament\Contador\Pages\DashboardContador;
use App\Filament\Contador\Pages\DeclaracionIva;
use App\Filament\Contador\Pages\EstadoResultados;
use App\Filament\Contador\Pages\LibroMayor;
use App\Filament\Contador\Pages\ReportesContador;
use App\Filament\Contador\Widgets\GraficoVentasMensualesWidget;
use App\Filament\Contador\Widgets\KPIsFinancierosWidget;
use App\Filament\Contador\Widgets\TopProductosWidget;
use App\Filament\Contador\Widgets\VentasSucursalWidget;
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

class ContadorPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('contador')
            ->path('contador')
            ->login()
            ->colors([
                'primary' => Color::Emerald,
                'gray' => Color::Slate,
                'info' => Color::Teal,
                'success' => Color::Green,
                'warning' => Color::Amber,
                'danger' => Color::Rose,
            ])
            ->font('Inter')
            ->brandName('TraceLog - Contabilidad')
            ->brandLogo(asset('images/logo.png'))
            ->brandLogoHeight('2.5rem')
            ->darkMode(false)
            ->sidebarCollapsibleOnDesktop()
            ->discoverResources(
                in: app_path('Filament/Contador/Resources'),
                for: 'App\\Filament\\Contador\\Resources'
            )
            ->navigationGroups([
                NavigationGroup::make('Contabilidad')->icon('heroicon-o-book-open'),
                NavigationGroup::make('Finanzas')->icon('heroicon-o-currency-dollar'),
                NavigationGroup::make('Reportes')->icon('heroicon-o-document-chart-bar'),
                NavigationGroup::make('Cuentas')->icon('heroicon-o-clipboard-document-list'),
            ])
            ->pages([
                DashboardContador::class,
                LibroMayor::class,
                BalanceGeneral::class,
                EstadoResultados::class,
                DeclaracionIva::class,
                ReportesContador::class,
                CuentasPorCobrar::class,
                CuentasPorPagar::class,
            ])
            ->widgets([
                KPIsFinancierosWidget::class,
                GraficoVentasMensualesWidget::class,
                TopProductosWidget::class,
                VentasSucursalWidget::class,
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
            ->breadcrumbs(true)
            ->maxContentWidth('full')
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s');
    }
}
