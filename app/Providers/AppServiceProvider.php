<?php

namespace App\Providers;

use App\Models\PedidoCompra;

use App\Models\PedidoVenta;
use App\Models\Traslado;
use App\Observers\TrasladoObserver;
use App\Observers\PedidoCompraObserver;
use App\Observers\PedidoVentaObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(LoginResponse::class, CustomLoginResponse::class);
    }

    public function boot(): void
    {
        // Registrar Observers para automatizar el inventario
        PedidoCompra::observe(PedidoCompraObserver::class);
        Traslado::observe(TrasladoObserver::class);
        PedidoVenta::observe(PedidoVentaObserver::class);
    }
}
