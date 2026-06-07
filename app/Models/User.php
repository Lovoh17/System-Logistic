<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'almacen_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            'admin'    => $this->hasRole('super_admin'),
            'sucursal' => $this->hasRole('admin_sucursal') && $this->almacen_id !== null,
            'ventas'   => $this->hasRole('cajero') && $this->almacen_id !== null,
            'logistica' => $this->hasAnyRole(['logistica', 'supervisor_bodega']),
            'contador' => $this->hasRole('contador'),
            default    => false,
        };
    }

    public static function getHomeUrl(): string
    {
        $user = auth()->user();

        if (!$user) {
            return '/admin/login';
        }

        if ($user->hasRole('super_admin')) {
            return '/admin';
        }

        if ($user->hasRole('admin_sucursal') && $user->almacen_id) {
            return '/sucursal';
        }

        if ($user->hasRole('cajero') && $user->almacen_id) {
            return '/ventas';
        }

        if ($user->hasRole('contador')) {
            return '/contador';
        }

        if ($user->hasAnyRole(['logistica', 'supervisor_bodega'])) {
            return '/logistica';
        }

        return '/admin/login';
    }
}
