<?php

namespace App\Providers;

use App\Models\PedidoCompra;
use App\Models\PedidoVenta;
use App\Observers\PedidoCompraObserver;
use App\Observers\PedidoVentaObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Registrar Observers para automatizar el inventario
        PedidoCompra::observe(PedidoCompraObserver::class);
        PedidoVenta::observe(PedidoVentaObserver::class);
    }
}
