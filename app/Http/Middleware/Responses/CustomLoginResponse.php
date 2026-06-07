<?php

namespace App\Http\Middleware\Responses;

use Filament\Http\Responses\Auth\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\RedirectResponse;

// Renombrado para evitar conflicto con la implementación principal en App\Http\Responses
class MiddlewareCustomLoginResponse implements LoginResponseContract
{
    public function toResponse($request): RedirectResponse
    {
        $user = auth()->user();

        if ($user->hasRole('super_admin')) {
            return new RedirectResponse(url('/admin'));
        }

        if ($user->hasRole('admin_sucursal')) {
            if (!$user->almacen_id) {
                return $this->sinSucursal($request, 'administrador de sucursal');
            }
            return new RedirectResponse(url('/sucursal'));
        }

        if ($user->hasRole('cajero')) {
            if (!$user->almacen_id) {
                return $this->sinSucursal($request, 'cajero');
            }
            return new RedirectResponse(url('/ventas'));
        }

        if ($user->hasRole('contador')) {
            return new RedirectResponse(url('/contador'));
        }

        if ($user->hasAnyRole(['logistica', 'supervisor_bodega'])) {
            return new RedirectResponse(url('/logistica'));
        }

        return $this->sinAcceso($request, 'Tu cuenta no tiene un rol asignado. Contacta al administrador.');
    }

    private function sinSucursal($request, string $rol): RedirectResponse
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('filament.admin.auth.login')
            ->withErrors(['email' => "Tu cuenta de {$rol} no tiene sucursal asignada. Contacta al administrador."]);
    }

    private function sinAcceso($request, string $mensaje): RedirectResponse
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('filament.admin.auth.login')
            ->withErrors(['email' => $mensaje]);
    }
}
