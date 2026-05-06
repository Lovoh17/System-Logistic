<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\RoleBasedAccess;

Route::get('/', function () {
    return redirect('/admin');
});

// Ruta para el dashboard de ventas (cajeros)
Route::middleware(['auth', RoleBasedAccess::class])->group(function () {
    Route::get('/ventas', function () {
        return view('ventas.dashboard');
    })->name('ventas.dashboard');
});

// Logout
Route::post('/logout', function () {
    auth()->logout();
    return redirect('/admin/login');
})->name('logout');