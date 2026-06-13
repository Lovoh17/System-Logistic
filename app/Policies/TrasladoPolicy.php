<?php

namespace App\Policies;

use App\Models\Traslado;
use App\Models\User;

/**
 * Mapea a permisos Spatie inventario.* (los traslados son movimientos de inventario).
 * El super_admin se autoriza globalmente vía Gate::before (AppServiceProvider).
 */
class TrasladoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('inventario.ver');
    }

    public function view(User $user, Traslado $traslado): bool
    {
        return $user->can('inventario.ver');
    }

    public function create(User $user): bool
    {
        return $user->can('inventario.traslados');
    }

    public function update(User $user, Traslado $traslado): bool
    {
        return $user->can('inventario.traslados');
    }

    public function delete(User $user, Traslado $traslado): bool
    {
        return $user->can('inventario.traslados');
    }
}
