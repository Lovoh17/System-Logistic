<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Almacen;
use Illuminate\Database\Seeder;

class AsignarSucursalesSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener todas las sucursales
        $sucursales = [
            'ss' => Almacen::where('nombre', 'like', '%San Salvador%')->first(),
            'sa' => Almacen::where('nombre', 'like', '%Santa Ana%')->first(),
            'sm' => Almacen::where('nombre', 'like', '%San Miguel%')->first(),
            'll' => Almacen::where('nombre', 'like', '%La Libertad%')->first(),
            'so' => Almacen::where('nombre', 'like', '%Sonsonate%')->first(),
            'us' => Almacen::where('nombre', 'like', '%Usulután%')->first(),
        ];

        // Asignar sucursales según el email
        $users = User::all();
        $asignados = 0;

        foreach ($users as $user) {
            $code = null;
            
            if (str_contains($user->email, '.ss@')) {
                $code = 'ss';
            } elseif (str_contains($user->email, '.sa@')) {
                $code = 'sa';
            } elseif (str_contains($user->email, '.sm@')) {
                $code = 'sm';
            } elseif (str_contains($user->email, '.ll@')) {
                $code = 'll';
            } elseif (str_contains($user->email, '.so@')) {
                $code = 'so';
            } elseif (str_contains($user->email, '.us@')) {
                $code = 'us';
            }

            if ($code && isset($sucursales[$code])) {
                $user->almacen_id = $sucursales[$code]->id;
                $user->save();
                $this->command->info("✅ {$user->email} → {$sucursales[$code]->nombre}");
                $asignados++;
            } else {
                // Super admin no tiene sucursal asignada
                if (str_contains($user->email, 'superadmin')) {
                    $user->almacen_id = null;
                    $user->save();
                    $this->command->info("👑 {$user->email} → Sin sucursal (Super Admin)");
                } else {
                    $this->command->warn("⚠️ {$user->email} → No se encontró sucursal");
                }
            }
        }

        $this->command->newLine();
        $this->command->info("📊 Total de usuarios con sucursal asignada: {$asignados}");
        $this->command->info("🎉 Asignación completada!");
    }
}