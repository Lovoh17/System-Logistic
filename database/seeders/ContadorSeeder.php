<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ContadorSeeder extends Seeder
{
    public function run(): void
    {
        // Crear rol contador
        $rol = Role::firstOrCreate(
            ['name' => 'contador', 'guard_name' => 'web'],
        );

        // Permisos necesarios para el contador
        $permisos = ['productos.ver', 'inventario.ver', 'compras.ver', 'ventas.ver', 'reportes.ver', 'reportes.exportar'];

        foreach ($permisos as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        $rol->syncPermissions($permisos);

        // Usuario contador de prueba
        $user = User::updateOrCreate(
            ['email' => 'contador@tracelog.com'],
            [
                'name'               => 'Contador General',
                'password'           => Hash::make('password'),
                'almacen_id'         => null,
                'email_verified_at'  => now(),
            ]
        );

        if (! $user->hasRole('contador')) {
            $user->assignRole('contador');
        }

        $this->command->info('');
        $this->command->info('Rol "contador" creado correctamente.');
        $this->command->info('Usuario: contador@tracelog.com | Contraseña: password');
        $this->command->info('Panel: /contador');
        $this->command->info('');
    }
}