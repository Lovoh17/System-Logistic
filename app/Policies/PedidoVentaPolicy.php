<?php

namespace App\Policies;

use App\Models\PedidoVenta;
use App\Models\User;

/**
 * Mapea a permisos Spatie ventas.*.
 * El super_admin se autoriza globalmente vía Gate::before (AppServiceProvider).
 */
class PedidoVentaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('ventas.ver');
    }

    public function view(User $user, PedidoVenta $pedido): bool
    {
        return $user->can('ventas.ver');
    }

    public function create(User $user): bool
    {
        return $user->can('ventas.crear');
    }

    public function update(User $user, PedidoVenta $pedido): bool
    {
        return $user->can('ventas.crear');
    }

    public function delete(User $user, PedidoVenta $pedido): bool
    {
        return $user->can('ventas.cancelar');
    }
}
