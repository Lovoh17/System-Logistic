<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Proveedor;
use App\Models\Cliente;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Transportista;
use App\Models\Almacen;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Usuarios del sistema ────────────────────────────
        User::factory()->create([
            'name'     => 'Administrador TraceLog',
            'email'    => 'admin@tracelog.com',
            'password' => Hash::make('password'),
        ]);

        User::factory()->create([
            'name'     => 'Coordinador Logístico',
            'email'    => 'logistica@tracelog.com',
            'password' => Hash::make('password'),
        ]);

        User::factory()->create([
            'name'     => 'Ejecutivo de Ventas',
            'email'    => 'ventas@tracelog.com',
            'password' => Hash::make('password'),
        ]);

        // ─── Almacenes ───────────────────────────────────────
        Almacen::create([
            'codigo'       => 'ALM-001',
            'nombre'       => 'Bodega Central San Salvador',
            'direccion'    => 'Calle Los Sisimiles, Col. Miramonte, San Salvador',
            'responsable'  => 'Juan Antonio García',
            'telefono'     => '2222-1111',
            'es_principal' => true,
            'activo'       => true,
        ]);

        Almacen::create([
            'codigo'    => 'ALM-002',
            'nombre'    => 'Bodega Santa Ana',
            'direccion' => 'Av. Independencia, Santa Ana',
            'activo'    => true,
        ]);

        // ─── Categorías ──────────────────────────────────────
        $catAlimentos   = Categoria::create(['nombre' => 'Alimentos y Bebidas', 'slug' => 'alimentos-bebidas', 'icono' => 'heroicon-o-cake', 'color' => '#f59e0b']);
        $catElect       = Categoria::create(['nombre' => 'Electrónica', 'slug' => 'electronica', 'icono' => 'heroicon-o-cpu-chip', 'color' => '#3b82f6']);
        $catLimpieza    = Categoria::create(['nombre' => 'Limpieza e Higiene', 'slug' => 'limpieza', 'icono' => 'heroicon-o-sparkles', 'color' => '#10b981']);
        $catOfi         = Categoria::create(['nombre' => 'Papelería y Oficina', 'slug' => 'papeleria-oficina', 'icono' => 'heroicon-o-document', 'color' => '#8b5cf6']);
        $catHerr        = Categoria::create(['nombre' => 'Ferretería', 'slug' => 'ferreteria', 'icono' => 'heroicon-o-wrench', 'color' => '#6b7280']);

        // ─── Proveedores ─────────────────────────────────────
        $prov1 = Proveedor::create([
            'codigo'            => 'PROV-00001',
            'nombre'            => 'Distribuidora La Selecta',
            'razon_social'      => 'La Selecta S.A. de C.V.',
            'nit'               => '0614-150390-101-5',
            'email'             => 'ventas@laselecta.com.sv',
            'telefono'          => '2222-3333',
            'contacto_nombre'   => 'María Elena Rodríguez',
            'contacto_email'    => 'mrodriguez@laselecta.com.sv',
            'pais'              => 'El Salvador',
            'departamento'      => 'San Salvador',
            'municipio'         => 'San Salvador',
            'direccion'         => 'Blvd. del Ejército Nacional Km 4.5',
            'categoria'         => 'general',
            'tiempo_entrega_dias' => 2,
            'calificacion'      => 4.5,
            'estado'            => 'activo',
        ]);

        $prov2 = Proveedor::create([
            'codigo'            => 'PROV-00002',
            'nombre'            => 'Tecnología Digital SV',
            'razon_social'      => 'Tecnología Digital S.A.',
            'nit'               => '0614-210891-003-2',
            'email'             => 'compras@tecnodigital.sv',
            'telefono'          => '2215-6789',
            'contacto_nombre'   => 'Roberto Flores',
            'pais'              => 'El Salvador',
            'departamento'      => 'La Libertad',
            'municipio'         => 'Antiguo Cuscatlán',
            'categoria'         => 'materia_prima',
            'tiempo_entrega_dias' => 5,
            'calificacion'      => 3.8,
            'estado'            => 'activo',
        ]);

        $prov3 = Proveedor::create([
            'codigo'            => 'PROV-00003',
            'nombre'            => 'Productos Químicos El Salvador',
            'email'             => 'info@pqes.com.sv',
            'telefono'          => '2244-5566',
            'pais'              => 'El Salvador',
            'departamento'      => 'Sonsonate',
            'categoria'         => 'materia_prima',
            'tiempo_entrega_dias' => 3,
            'calificacion'      => 4.2,
            'estado'            => 'activo',
        ]);

        // ─── Clientes ────────────────────────────────────────
        Cliente::create([
            'codigo'              => 'CLI-00001',
            'nombre'              => 'Supermercado El Colono',
            'razon_social'        => 'Distribuidora El Colono S.A.',
            'nit'                 => '0614-290580-014-7',
            'email'               => 'compras@elcolono.com.sv',
            'telefono'            => '2333-4444',
            'departamento'        => 'San Salvador',
            'municipio'           => 'Mejicanos',
            'direccion_principal' => 'Av. Bernal #45, Mejicanos, San Salvador',
            'tipo'                => 'mayorista',
            'limite_credito'      => 15000.00,
            'dias_credito'        => 30,
            'estado'              => 'activo',
        ]);

        Cliente::create([
            'codigo'              => 'CLI-00002',
            'nombre'              => 'Farmacia San Nicolás',
            'email'               => 'pedidos@fsn.com',
            'telefono'            => '2211-9988',
            'departamento'        => 'Santa Ana',
            'municipio'           => 'Santa Ana',
            'tipo'                => 'minorista',
            'limite_credito'      => 5000.00,
            'dias_credito'        => 15,
            'estado'              => 'activo',
        ]);

        Cliente::create([
            'codigo'              => 'CLI-00003',
            'nombre'              => 'Hotel Sheraton Presidente',
            'nit'                 => '0614-010175-021-9',
            'email'               => 'suministros@sheraton.sv',
            'telefono'            => '2283-4000',
            'departamento'        => 'San Salvador',
            'municipio'           => 'San Salvador',
            'tipo'                => 'corporativo',
            'limite_credito'      => 50000.00,
            'dias_credito'        => 45,
            'estado'              => 'activo',
        ]);

        Cliente::create([
            'codigo'              => 'CLI-00004',
            'nombre'              => 'Restaurante La Pampa',
            'telefono'            => '2278-1234',
            'departamento'        => 'San Salvador',
            'municipio'           => 'San Salvador',
            'tipo'                => 'minorista',
            'estado'              => 'activo',
        ]);

        // ─── Productos ───────────────────────────────────────
        Producto::create([
            'codigo'        => 'PROD-000001',
            'sku'           => 'SAL-001-1KG',
            'nombre'        => 'Sal de Mesa Refinada 1kg',
            'categoria_id'  => $catAlimentos->id,
            'proveedor_id'  => $prov1->id,
            'unidad_medida' => 'unidad',
            'precio_compra' => 0.45,
            'precio_venta'  => 0.75,
            'stock_actual'  => 850,
            'stock_minimo'  => 100,
            'stock_maximo'  => 2000,
            'peso_kg'       => 1.0,
            'ubicacion_almacen' => 'A-01-01',
            'estado'        => 'activo',
        ]);

        Producto::create([
            'codigo'        => 'PROD-000002',
            'sku'           => 'AZU-001-1KG',
            'nombre'        => 'Azúcar Blanca 1kg',
            'categoria_id'  => $catAlimentos->id,
            'proveedor_id'  => $prov1->id,
            'unidad_medida' => 'unidad',
            'precio_compra' => 0.65,
            'precio_venta'  => 0.95,
            'stock_actual'  => 40,
            'stock_minimo'  => 200,
            'stock_maximo'  => 3000,
            'peso_kg'       => 1.0,
            'ubicacion_almacen' => 'A-01-02',
            'estado'        => 'activo',
        ]);

        Producto::create([
            'codigo'        => 'PROD-000003',
            'sku'           => 'ACE-SOY-1L',
            'nombre'        => 'Aceite de Soya 1 litro',
            'categoria_id'  => $catAlimentos->id,
            'proveedor_id'  => $prov1->id,
            'unidad_medida' => 'unidad',
            'precio_compra' => 1.80,
            'precio_venta'  => 2.50,
            'stock_actual'  => 320,
            'stock_minimo'  => 100,
            'stock_maximo'  => 1500,
            'ubicacion_almacen' => 'A-02-01',
            'estado'        => 'activo',
        ]);

        Producto::create([
            'codigo'        => 'PROD-000004',
            'sku'           => 'ELEC-CAB-USB',
            'nombre'        => 'Cable USB-C 1m Premium',
            'categoria_id'  => $catElect->id,
            'proveedor_id'  => $prov2->id,
            'unidad_medida' => 'unidad',
            'precio_compra' => 2.50,
            'precio_venta'  => 6.99,
            'stock_actual'  => 0,
            'stock_minimo'  => 20,
            'stock_maximo'  => 200,
            'ubicacion_almacen' => 'B-01-01',
            'estado'        => 'activo',
        ]);

        Producto::create([
            'codigo'        => 'PROD-000005',
            'sku'           => 'DET-500G-BLAN',
            'nombre'        => 'Detergente en Polvo 500g',
            'categoria_id'  => $catLimpieza->id,
            'proveedor_id'  => $prov3->id,
            'unidad_medida' => 'unidad',
            'precio_compra' => 0.90,
            'precio_venta'  => 1.45,
            'stock_actual'  => 1200,
            'stock_minimo'  => 150,
            'stock_maximo'  => 3000,
            'ubicacion_almacen' => 'C-01-01',
            'estado'        => 'activo',
        ]);

        Producto::create([
            'codigo'        => 'PROD-000006',
            'sku'           => 'ARROZ-SV-1KG',
            'nombre'        => 'Arroz Grano de Oro 1kg',
            'categoria_id'  => $catAlimentos->id,
            'proveedor_id'  => $prov1->id,
            'unidad_medida' => 'unidad',
            'precio_compra' => 0.72,
            'precio_venta'  => 1.10,
            'stock_actual'  => 2800,
            'stock_minimo'  => 300,
            'stock_maximo'  => 5000,
            'peso_kg'       => 1.0,
            'ubicacion_almacen' => 'A-03-01',
            'es_perecedero' => true,
            'vida_util_dias' => 730,
            'estado'        => 'activo',
        ]);

        // ─── Transportistas ──────────────────────────────────
        Transportista::create([
            'codigo'           => 'TRANS-0001',
            'nombre'           => 'Carlos Ernesto Mejía (Camión Propio)',
            'tipo'             => 'propio',
            'vehiculo_tipo'    => 'camion',
            'vehiculo_placa'   => 'N 123456',
            'vehiculo_modelo'  => 'Isuzu NKR 2020',
            'capacidad_kg'     => 5000,
            'capacidad_m3'     => 20,
            'conductor_nombre' => 'Carlos Ernesto Mejía',
            'conductor_licencia' => 'LIC-001-2024',
            'conductor_telefono' => '7888-1234',
            'tiene_gps'        => true,
            'tarifa_km'        => 0.45,
            'estado'           => 'disponible',
        ]);

        Transportista::create([
            'codigo'           => 'TRANS-0002',
            'nombre'           => 'Flota Express SV - Pickup',
            'tipo'             => 'externo',
            'vehiculo_tipo'    => 'pickup',
            'vehiculo_placa'   => 'P 987654',
            'vehiculo_modelo'  => 'Toyota Hilux 2022',
            'capacidad_kg'     => 1000,
            'conductor_nombre' => 'Roberto Alfredo Pérez',
            'conductor_telefono' => '7777-5678',
            'tiene_gps'        => false,
            'tarifa_fija'      => 35.00,
            'estado'           => 'disponible',
        ]);

        Transportista::create([
            'codigo'           => 'TRANS-0003',
            'nombre'           => 'Frío Express (Refrigerado)',
            'tipo'             => 'externo',
            'vehiculo_tipo'    => 'furgon',
            'vehiculo_placa'   => 'F 456789',
            'capacidad_kg'     => 3000,
            'conductor_nombre' => 'Ana Patricia Villalta',
            'conductor_telefono' => '7999-4321',
            'tiene_refrigeracion' => true,
            'tiene_gps'        => true,
            'tarifa_km'        => 0.65,
            'estado'           => 'en_ruta',
        ]);

        $this->command->info('✅ TraceLog: Base de datos sembrada exitosamente.');
        $this->command->info('');
        $this->command->info('👤 Credenciales de acceso:');
        $this->command->info('   Admin: admin@tracelog.com / password');
        $this->command->info('   Logística: logistica@tracelog.com / password');
        $this->command->info('   Ventas: ventas@tracelog.com / password');
    }
}
