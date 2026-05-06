<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleBasedAccess
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        // Verificar si el usuario está autenticado y es cajero
        if (auth()->check() && auth()->user()->rol === 'cajero') {
            if ($request->path() === 'admin' || str_starts_with($request->path(), 'admin/')) {
                return redirect('/ventas');
            }
        }
        
        return $response;
    }
}