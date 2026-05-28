<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;  // ← agregar este use

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, HasRoles; 

    protected $fillable = [
        'name',
        'email',
        'password',
        'rol', 
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

    /**
     * Todos los usuarios autenticados tienen acceso al panel Filament.
     * Puedes agregar lógica de roles aquí con Spatie Permissions.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public static function getHomeUrl(): string
{
    $user = auth()->user();
    
    if (!$user) {
        return '/login';
    }
    
    if ($user->hasRole('super-admin') || $user->hasRole('admin-sucursal')) {
        return '/admin';
    }
    
    if ($user->hasRole('cajero')) {
        return '/ventas';
    }

    if ($user->hasRole('contador')) {
        return '/contador';
    }

    if ($user->hasRole('logistica') || $user->hasRole('supervisor-bodega')) {
        return '/admin';
    }

    return '/admin'; // Por defecto
}

    
}
