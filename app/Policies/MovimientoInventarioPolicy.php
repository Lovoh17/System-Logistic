<?php

namespace App\Policies;

use App\Models\MovimientoInventario;
use App\Models\User;

/**
 * Kardex de inventario. Mapea a permisos Spatie inventario.*.
 * Los movimientos son inmutables (no update/delete), igual que en el Resource.
 * El super_admin se autoriza globalmente vía Gate::before (AppServiceProvider).
 */
class MovimientoInventarioPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('inventario.ver');
    }

    public function view(User $user, MovimientoInventario $movimiento): bool
    {
        return $user->can('inventario.ver');
    }

    public function create(User $user): bool
    {
        return $user->can('inventario.ajustar');
    }

    public function update(User $user, MovimientoInventario $movimiento): bool
    {
        return false;
    }

    public function delete(User $user, MovimientoInventario $movimiento): bool
    {
        return false;
    }
}
