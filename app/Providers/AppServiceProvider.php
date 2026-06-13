<?php

namespace App\Providers;

use App\Http\Responses\CustomLoginResponse;
use App\Models\PedidoCompra;
use App\Models\PedidoVenta;
use App\Models\Traslado;
use App\Observers\PedidoCompraObserver;
use App\Observers\PedidoVentaObserver;
use App\Observers\TrasladoObserver;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Telescope\TelescopeServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(LoginResponse::class, CustomLoginResponse::class);

        // Telescope es una dependencia de desarrollo (require-dev) sin auto-discovery:
        // se registra solo en local y solo si el paquete está instalado, evitando
        // que producción (composer install --no-dev) falle por la clase ausente.
        if ($this->app->environment('local') && class_exists(TelescopeServiceProvider::class)) {
            $this->app->register(TelescopeServiceProvider::class);
            $this->app->register(\App\Providers\TelescopeServiceProvider::class);
        }
    }

    public function boot(): void
    {
        PedidoCompra::observe(PedidoCompraObserver::class);
        Traslado::observe(TrasladoObserver::class);
        PedidoVenta::observe(PedidoVentaObserver::class);

        // El super_admin tiene acceso total: omite cualquier verificación de policy.
        Gate::before(fn ($user, $ability) => $user->hasRole('super_admin') ? true : null);
    }
}
