<?php

namespace App\Policies;

use App\Models\Transportista;
use App\Models\User;

/**
 * Mapea a permisos Spatie transportistas.*.
 * El super_admin se autoriza globalmente vía Gate::before (AppServiceProvider).
 */
class TransportistaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('transportistas.ver');
    }

    public function view(User $user, Transportista $transportista): bool
    {
        return $user->can('transportistas.ver');
    }

    public function create(User $user): bool
    {
        return $user->can('transportistas.gestionar');
    }

    public function update(User $user, Transportista $transportista): bool
    {
        return $user->can('transportistas.gestionar');
    }

    public function delete(User $user, Transportista $transportista): bool
    {
        return $user->can('transportistas.gestionar');
    }
}
