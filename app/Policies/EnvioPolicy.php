<?php

namespace App\Policies;

use App\Models\Envio;
use App\Models\User;

/**
 * Mapea a permisos Spatie envios.*.
 * El super_admin se autoriza globalmente vía Gate::before (AppServiceProvider).
 */
class EnvioPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('envios.ver');
    }

    public function view(User $user, Envio $envio): bool
    {
        return $user->can('envios.ver');
    }

    public function create(User $user): bool
    {
        return $user->can('envios.crear');
    }

    public function update(User $user, Envio $envio): bool
    {
        return $user->can('envios.gestionar');
    }

    public function delete(User $user, Envio $envio): bool
    {
        return $user->can('envios.gestionar');
    }
}
