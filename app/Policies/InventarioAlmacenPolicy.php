<?php

namespace App\Policies;

use App\Models\InventarioAlmacen;
use App\Models\User;

/**
 * Stock por sucursal. Mapea a permisos Spatie inventario.*.
 * Solo permite ver y ajustar parámetros (stock mínimo/máximo/reorden);
 * no se crean ni eliminan registros desde la UI.
 * El super_admin se autoriza globalmente vía Gate::before (AppServiceProvider).
 */
class InventarioAlmacenPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('inventario.ver');
    }

    public function view(User $user, InventarioAlmacen $inventario): bool
    {
        return $user->can('inventario.ver');
    }

    public function create(User $user): bool
    {
        return $user->can('inventario.ajustar');
    }

    public function update(User $user, InventarioAlmacen $inventario): bool
    {
        return $user->can('inventario.ajustar');
    }

    public function delete(User $user, InventarioAlmacen $inventario): bool
    {
        return false;
    }
}
