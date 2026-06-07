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
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(LoginResponse::class, CustomLoginResponse::class);
    }

    public function boot(): void
    {
        PedidoCompra::observe(PedidoCompraObserver::class);
        Traslado::observe(TrasladoObserver::class);
        PedidoVenta::observe(PedidoVentaObserver::class);
    }
}
