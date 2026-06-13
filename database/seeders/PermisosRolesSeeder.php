<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Crea/actualiza roles y permisos Spatie y los asigna a cada rol.
 *
 * Es idempotente (firstOrCreate + syncPermissions): puede ejecutarse de forma
 * aislada sobre una BD ya poblada con:
 *   php artisan db:seed --class=Database\\Seeders\\PermisosRolesSeeder
 *
 * Las asignaciones reflejan el acceso real de cada panel Filament.
 */
class PermisosRolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'super_admin', 'admin_sucursal', 'cajero',
            'supervisor_bodega', 'logistica', 'contador', 'transportista',
        ];

        foreach ($roles as $nombre) {
            Role::firstOrCreate(['name' => $nombre, 'guard_name' => 'web']);
        }

        $permisos = [
            'usuarios.ver', 'usuarios.crear', 'usuarios.editar', 'usuarios.eliminar',
            'productos.ver', 'productos.crear', 'productos.editar', 'productos.eliminar',
            'inventario.ver', 'inventario.ajustar', 'inventario.traslados',
            'compras.ver', 'compras.crear', 'compras.aprobar', 'compras.cancelar',
            'ventas.ver', 'ventas.crear', 'ventas.aprobar', 'ventas.cancelar',
            'envios.ver', 'envios.crear', 'envios.despachar', 'envios.gestionar',
            'transportistas.ver', 'transportistas.gestionar',
            'reportes.ver', 'reportes.exportar',
            'config.ver', 'config.editar',
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'web']);
        }

        $matriz = [
            'super_admin' => Permission::all()->pluck('name')->all(),

            'admin_sucursal' => [
                'productos.ver', 'productos.crear', 'productos.editar',
                'inventario.ver', 'inventario.ajustar', 'inventario.traslados',
                'compras.ver', 'compras.crear', 'compras.aprobar',
                'ventas.ver', 'ventas.crear', 'ventas.aprobar', 'ventas.cancelar',
                'envios.ver', 'envios.crear', 'envios.despachar',
                'transportistas.ver', 'transportistas.gestionar',
                'reportes.ver', 'reportes.exportar',
            ],

            'cajero' => [
                'productos.ver',
                'inventario.ver',
                'ventas.ver', 'ventas.crear',
                'envios.ver',
                'reportes.ver',
            ],

            'supervisor_bodega' => [
                'productos.ver', 'productos.editar',
                'inventario.ver', 'inventario.ajustar', 'inventario.traslados',
                'compras.ver', 'compras.crear',
                'ventas.ver',
                'envios.ver',
                'transportistas.ver',
                'reportes.ver',
            ],

            // Coordinador logístico: gestiona compras, ventas, envíos y transportistas
            // (alineado con los recursos que expone el panel de logística).
            'logistica' => [
                'productos.ver',
                'inventario.ver', 'inventario.traslados',
                'compras.ver', 'compras.crear', 'compras.aprobar',
                'ventas.ver', 'ventas.crear',
                'envios.ver', 'envios.crear', 'envios.despachar', 'envios.gestionar',
                'transportistas.ver', 'transportistas.gestionar',
                'reportes.ver', 'reportes.exportar',
            ],

            'contador' => [
                'productos.ver',
                'inventario.ver',
                'compras.ver',
                'ventas.ver',
                'reportes.ver', 'reportes.exportar',
            ],

            'transportista' => [
                'envios.ver',
            ],
        ];

        foreach ($matriz as $rol => $permisosRol) {
            Role::findByName($rol, 'web')->syncPermissions($permisosRol);
        }
    }
}
