<?php

namespace App\Policies;

use App\Models\PedidoCompra;
use App\Models\User;

/**
 * Mapea a permisos Spatie compras.*.
 * El super_admin se autoriza globalmente vía Gate::before (AppServiceProvider).
 */
class PedidoCompraPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('compras.ver');
    }

    public function view(User $user, PedidoCompra $pedido): bool
    {
        return $user->can('compras.ver');
    }

    public function create(User $user): bool
    {
        return $user->can('compras.crear');
    }

    public function update(User $user, PedidoCompra $pedido): bool
    {
        return $user->can('compras.crear');
    }

    public function delete(User $user, PedidoCompra $pedido): bool
    {
        return $user->can('compras.cancelar');
    }
}
