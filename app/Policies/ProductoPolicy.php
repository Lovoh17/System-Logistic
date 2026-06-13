<?php

namespace App\Policies;

use App\Models\Producto;
use App\Models\User;

/**
 * Mapea las acciones a los permisos Spatie productos.*.
 * El super_admin se autoriza globalmente vía Gate::before (AppServiceProvider).
 */
class ProductoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('productos.ver');
    }

    public function view(User $user, Producto $producto): bool
    {
        return $user->can('productos.ver');
    }

    public function create(User $user): bool
    {
        return $user->can('productos.crear');
    }

    public function update(User $user, Producto $producto): bool
    {
        return $user->can('productos.editar');
    }

    public function delete(User $user, Producto $producto): bool
    {
        return $user->can('productos.eliminar');
    }

    public function restore(User $user, Producto $producto): bool
    {
        return $user->can('productos.eliminar');
    }

    public function forceDelete(User $user, Producto $producto): bool
    {
        return $user->can('productos.eliminar');
    }
}
