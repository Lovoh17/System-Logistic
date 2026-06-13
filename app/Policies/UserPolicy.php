<?php

namespace App\Policies;

use App\Models\User;

/**
 * Mapea las acciones a los permisos Spatie usuarios.*.
 * El super_admin se autoriza globalmente vía Gate::before (AppServiceProvider).
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('usuarios.ver');
    }

    public function view(User $user, User $model): bool
    {
        return $user->can('usuarios.ver');
    }

    public function create(User $user): bool
    {
        return $user->can('usuarios.crear');
    }

    public function update(User $user, User $model): bool
    {
        return $user->can('usuarios.editar');
    }

    public function delete(User $user, User $model): bool
    {
        // Nadie puede eliminarse a sí mismo.
        return $user->can('usuarios.eliminar') && $user->id !== $model->id;
    }
}
