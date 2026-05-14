<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Almacen;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

/**
 * AsignarSucursalesSeeder
 *
 * Útil para re-sincronizar almacen_id y roles en entornos donde los usuarios
 * ya existen (ej: después de un reset parcial o migración incremental).
 *
 * Si corres DatabaseSeeder completo desde cero, este seeder NO es necesario
 * porque DatabaseSeeder ya asigna almacen_id y roles en el mismo paso.
 */
class AsignarSucursalesSeeder extends Seeder
{
    public function run(): void
    {
        $sucursales = [
            'ss' => Almacen::where('nombre', 'like', '%San Salvador%')->first(),
            'sa' => Almacen::where('nombre', 'like', '%Santa Ana%')->first(),
            'sm' => Almacen::where('nombre', 'like', '%San Miguel%')->first(),
            'll' => Almacen::where('nombre', 'like', '%La Libertad%')->first(),
            'so' => Almacen::where('nombre', 'like', '%Sonsonate%')->first(),
            'us' => Almacen::where('nombre', 'like', '%Usulután%')->first(),
        ];

        // Mapa email-pattern → [ almacen_key, rol_spatie ]
        $patronesRol = [
            'admin.'  => 'admin_sucursal',
            'cajero.' => 'cajero',
            'bodega.' => 'supervisor_bodega',
        ];

        $users    = User::all();
        $asignados = 0;

        foreach ($users as $user) {
            // ── Determinar sucursal ────────────────────────────────────────
            $codigoSucursal = null;
            foreach (array_keys($sucursales) as $code) {
                if (str_contains($user->email, ".{$code}@")) {
                    $codigoSucursal = $code;
                    break;
                }
            }

            // ── Asignar almacen_id ─────────────────────────────────────────
            if ($codigoSucursal && isset($sucursales[$codigoSucursal])) {
                $almacen = $sucursales[$codigoSucursal];
                $user->almacen_id = $almacen->id;
                $user->save();
                $this->command->info("✅ {$user->email} → {$almacen->nombre}");
                $asignados++;
            } elseif (str_contains($user->email, 'superadmin') || str_contains($user->email, 'logistica')) {
                $user->almacen_id = null;
                $user->save();
                $this->command->info("🌐 {$user->email} → Sin sucursal (usuario global)");
            } else {
                $this->command->warn("⚠️  {$user->email} → No se encontró sucursal");
            }

            // ── Asignar rol Spatie si el usuario aún no tiene ninguno ──────
            if ($user->roles->isEmpty()) {
                $rolAsignado = null;

                if (str_contains($user->email, 'superadmin')) {
                    $rolAsignado = 'super_admin';
                } elseif (str_contains($user->email, 'logistica')) {
                    $rolAsignado = 'logistica';
                } else {
                    foreach ($patronesRol as $patron => $rol) {
                        if (str_contains($user->email, $patron)) {
                            $rolAsignado = $rol;
                            break;
                        }
                    }
                }

                if ($rolAsignado) {
                    $role = Role::where('name', $rolAsignado)->first();
                    if ($role) {
                        $user->assignRole($role);
                        $this->command->line("   🏷️  Rol asignado: {$rolAsignado}");
                    } else {
                        $this->command->warn("   ⚠️  Rol '{$rolAsignado}' no existe. ¿Corriste DatabaseSeeder primero?");
                    }
                }
            } else {
                $this->command->line("   ⏭️  Ya tiene rol: {$user->roles->pluck('name')->join(', ')}");
            }
        }

        $this->command->newLine();
        $this->command->info("📊 Usuarios con sucursal asignada: {$asignados}");
        $this->command->info("🎉 Sincronización completada.");
    }
}
