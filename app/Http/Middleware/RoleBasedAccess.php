<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleBasedAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar si el usuario está autenticado
        if (auth()->check()) {
            $user = auth()->user();
            
            // Obtener el rol del usuario (Spatie)
            $role = $user->roles->first();
            $roleName = $role?->name;
            
            // Redirecciones según el rol
            switch ($roleName) {
                case 'cajero':
                    // Cajero no puede acceder al panel de administración
                    if ($request->path() === 'admin' || str_starts_with($request->path(), 'admin/')) {
                        return redirect('/ventas')
                            ->with('warning', 'Acceso denegado. No tienes permisos de administrador.');
                    }
                    break;

                case 'contador':
                    // Contador solo puede acceder a su panel
                    if ($request->path() === 'admin' || str_starts_with($request->path(), 'admin/')) {
                        return redirect('/contador')
                            ->with('info', 'Redirigido al panel de contabilidad.');
                    }
                    break;
                    
                case 'supervisor_bodega':
                    // Supervisor de bodega tiene acceso limitado
                    if ($request->path() === 'admin' && !str_starts_with($request->path(), 'admin/inventario')) {
                        if (!str_starts_with($request->path(), 'admin/inventario')) {
                            return redirect()->route('inventario.index')
                                ->with('info', 'Acceso limitado al módulo de inventario.');
                        }
                    }
                    break;
                    
                case 'logistica':
                    // Logística solo puede acceder a módulos de envíos
                    if ($request->path() === 'admin' && !str_starts_with($request->path(), 'admin/envios')) {
                        if (!str_starts_with($request->path(), 'admin/envios')) {
                            return redirect()->route('envios.index')
                                ->with('info', 'Redirigido al módulo de envíos.');
                        }
                    }
                    break;
                    
                case 'admin_sucursal':
                    // Administrador de sucursal tiene acceso completo pero solo a su sucursal
                    // (esto se maneja en políticas, no en middleware)
                    break;
                    
                case 'super_admin':
                    // Super admin tiene acceso total
                    break;
                    
                default:
                    // Sin rol asignado
                    if ($request->path() === 'admin' || str_starts_with($request->path(), 'admin/')) {
                        auth()->logout();
                        return redirect()->route('login')
                            ->with('error', 'Usuario sin rol asignado. Contacte al administrador.');
                    }
                    break;
            }
        }
        
        return $next($request);
    }
}