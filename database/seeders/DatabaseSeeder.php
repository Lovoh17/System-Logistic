<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Proveedor;
use App\Models\Cliente;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Transportista;
use App\Models\Almacen;
use App\Models\InventarioAlmacen;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ═══════════════════════════════════════════════════════════════════
        // 1. CATEGORÍAS (Ferretería, Agricultura, Construcción, etc.)
        // ═══════════════════════════════════════════════════════════════════
        
        $categorias = [
            ['nombre' => 'Herramientas Eléctricas', 'slug' => 'herramientas-electricas', 'icono' => 'heroicon-o-bolt', 'color' => '#f59e0b'],
            ['nombre' => 'Herramientas Manuales', 'slug' => 'herramientas-manuales', 'icono' => 'heroicon-o-wrench', 'color' => '#6b7280'],
            ['nombre' => 'Materiales de Construcción', 'slug' => 'materiales-construccion', 'icono' => 'heroicon-o-building', 'color' => '#ef4444'],
            ['nombre' => 'Fertilizantes y Agroquímicos', 'slug' => 'fertilizantes', 'icono' => 'heroicon-o-sparkles', 'color' => '#10b981'],
            ['nombre' => 'Pinturas y Acabados', 'slug' => 'pinturas', 'icono' => 'heroicon-o-paint-brush', 'color' => '#8b5cf6'],
            ['nombre' => 'Ferretería en General', 'slug' => 'ferreteria-general', 'icono' => 'heroicon-o-cog', 'color' => '#3b82f6'],
            ['nombre' => 'Jardinería y Riego', 'slug' => 'jardineria', 'icono' => 'heroicon-o-flower', 'color' => '#22c55e'],
            ['nombre' => 'Seguridad Industrial', 'slug' => 'seguridad', 'icono' => 'heroicon-o-shield-check', 'color' => '#eab308'],
            ['nombre' => 'Plomería', 'slug' => 'plomeria', 'icono' => 'heroicon-o-droplet', 'color' => '#06b6d4'],
            ['nombre' => 'Electricidad', 'slug' => 'electricidad', 'icono' => 'heroicon-o-light-bulb', 'color' => '#f97316'],
        ];
        
        foreach ($categorias as $cat) {
            Categoria::create($cat);
        }

        // ═══════════════════════════════════════════════════════════════════
        // 2. PROVEEDORES
        // ═══════════════════════════════════════════════════════════════════
        
        $proveedores = [
            ['codigo' => 'PROV-001', 'nombre' => 'Truper El Salvador', 'email' => 'ventas@truper.com.sv', 'telefono' => '2244-1111', 'categoria' => 'materia_prima', 'tiempo_entrega_dias' => 3, 'calificacion' => 4.8],
            ['codigo' => 'PROV-002', 'nombre' => 'Pinturas Comex', 'email' => 'pedidos@comex.com', 'telefono' => '2222-2222', 'categoria' => 'materia_prima', 'tiempo_entrega_dias' => 4, 'calificacion' => 4.5],
            ['codigo' => 'PROV-003', 'nombre' => 'Holcim El Salvador', 'email' => 'ventas@holcim.com.sv', 'telefono' => '2244-3333', 'categoria' => 'materia_prima', 'tiempo_entrega_dias' => 5, 'calificacion' => 4.9],
            ['codigo' => 'PROV-004', 'nombre' => 'Bayer Crop Science', 'email' => 'agricultura@bayer.com', 'telefono' => '2244-4444', 'categoria' => 'materia_prima', 'tiempo_entrega_dias' => 6, 'calificacion' => 4.7],
            ['codigo' => 'PROV-005', 'nombre' => 'Makita El Salvador', 'email' => 'ventas@makita.com.sv', 'telefono' => '2244-5555', 'categoria' => 'materia_prima', 'tiempo_entrega_dias' => 3, 'calificacion' => 4.9],
            ['codigo' => 'PROV-006', 'nombre' => 'Distribuidora de Materiales Ponce', 'email' => 'ventas@ponce.com', 'telefono' => '2244-6666', 'categoria' => 'general', 'tiempo_entrega_dias' => 2, 'calificacion' => 4.3],
            ['codigo' => 'PROV-007', 'nombre' => 'Agro Insumos S.A.', 'email' => 'ventas@agroinsumos.com', 'telefono' => '2244-7777', 'categoria' => 'materia_prima', 'tiempo_entrega_dias' => 5, 'calificacion' => 4.4],
        ];
        
        foreach ($proveedores as $prov) {
            Proveedor::create(array_merge($prov, [
                'pais' => 'El Salvador',
                'departamento' => 'San Salvador',
                'estado' => 'activo',
            ]));
        }

        // ═══════════════════════════════════════════════════════════════════
        // 3. ALMACENES (6 SUCURSALES)
        // ═══════════════════════════════════════════════════════════════════
        
        $almacenesData = [
            ['codigo' => 'ALM-001', 'nombre' => 'San Salvador (Central)', 'direccion' => 'Blvd. Constitución #1500, San Salvador', 'responsable' => 'Carlos Martínez', 'telefono' => '2222-1111', 'es_principal' => true],
            ['codigo' => 'ALM-002', 'nombre' => 'Santa Ana', 'direccion' => 'Final Av. Independencia #45, Santa Ana', 'responsable' => 'Ana Rodríguez', 'telefono' => '2444-2222', 'es_principal' => false],
            ['codigo' => 'ALM-003', 'nombre' => 'San Miguel', 'direccion' => 'Calle El Progreso #78, San Miguel', 'responsable' => 'José García', 'telefono' => '2666-3333', 'es_principal' => false],
            ['codigo' => 'ALM-004', 'nombre' => 'La Libertad', 'direccion' => 'Carretera Litoral Km 32, La Libertad', 'responsable' => 'Marlene López', 'telefono' => '2333-4444', 'es_principal' => false],
            ['codigo' => 'ALM-005', 'nombre' => 'Sonsonate', 'direccion' => '6a Calle Poniente #23, Sonsonate', 'responsable' => 'Roberto Méndez', 'telefono' => '2444-5555', 'es_principal' => false],
            ['codigo' => 'ALM-006', 'nombre' => 'Usulután', 'direccion' => 'Av. Francisco Funes #12, Usulután', 'responsable' => 'Lilian Flores', 'telefono' => '2666-6666', 'es_principal' => false],
        ];
        
        $almacenes = [];
        foreach ($almacenesData as $data) {
            $almacenes[] = Almacen::create($data);
        }

        // ═══════════════════════════════════════════════════════════════════
        // 4. USUARIOS POR SUCURSAL
        // ═══════════════════════════════════════════════════════════════════
        
        // Super Admin (ve todo)
        User::create([
            'name' => 'Super Administrador',
            'email' => 'superadmin@tracelog.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        
        // Usuario por cada sucursal: Admin, Cajero, Supervisor Bodega
        $rolesPorSucursal = [
            1 => ['San Salvador', 'admin.ss', 'cajero.ss', 'bodega.ss'],
            2 => ['Santa Ana', 'admin.sa', 'cajero.sa', 'bodega.sa'],
            3 => ['San Miguel', 'admin.sm', 'cajero.sm', 'bodega.sm'],
            4 => ['La Libertad', 'admin.ll', 'cajero.ll', 'bodega.ll'],
            5 => ['Sonsonate', 'admin.so', 'cajero.so', 'bodega.so'],
            6 => ['Usulután', 'admin.us', 'cajero.us', 'bodega.us'],
        ];
        
        foreach ($rolesPorSucursal as $sucursalId => [$nombreSucursal, $adminEmail, $cajeroEmail, $bodegaEmail]) {
            // Administrador de sucursal
            User::create([
                'name' => "Administrador {$nombreSucursal}",
                'email' => "{$adminEmail}@tracelog.com",
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
            
            // Cajero de sucursal
            User::create([
                'name' => "Cajero {$nombreSucursal}",
                'email' => "{$cajeroEmail}@tracelog.com",
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
            
            // Supervisor de Bodega
            User::create([
                'name' => "Supervisor Bodega {$nombreSucursal}",
                'email' => "{$bodegaEmail}@tracelog.com",
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
        }

        // ═══════════════════════════════════════════════════════════════════
        // 5. PRODUCTOS (100+ productos de ferretería/agricultura)
        // ═══════════════════════════════════════════════════════════════════
        
        $productosData = [
            // Herramientas Eléctricas (categoría 1)
            ['codigo' => 'PROD-001', 'sku' => 'TAL-001', 'nombre' => 'Taladro Percutor 1/2" 650W', 'precio_compra' => 45.00, 'precio_venta' => 69.99, 'stock_minimo' => 5, 'stock_maximo' => 30, 'categoria' => 1],
            ['codigo' => 'PROD-002', 'sku' => 'ROT-001', 'nombre' => 'Rotomartillo SDS Plus 800W', 'precio_compra' => 85.00, 'precio_venta' => 129.99, 'stock_minimo' => 3, 'stock_maximo' => 20, 'categoria' => 1],
            ['codigo' => 'PROD-003', 'sku' => 'ESM-001', 'nombre' => 'Esmeril Angular 4-1/2" 850W', 'precio_compra' => 32.00, 'precio_venta' => 49.99, 'stock_minimo' => 8, 'stock_maximo' => 40, 'categoria' => 1],
            ['codigo' => 'PROD-004', 'sku' => 'SIE-001', 'nombre' => 'Sierra Caladora 550W', 'precio_compra' => 38.00, 'precio_venta' => 59.99, 'stock_minimo' => 4, 'stock_maximo' => 25, 'categoria' => 1],
            ['codigo' => 'PROD-005', 'sku' => 'DIS-001', 'nombre' => 'Disco de Corte 4-1/2" (10 uds)', 'precio_compra' => 5.00, 'precio_venta' => 9.99, 'stock_minimo' => 20, 'stock_maximo' => 100, 'categoria' => 1],
            
            // Herramientas Manuales (categoría 2)
            ['codigo' => 'PROD-006', 'sku' => 'JGO-001', 'nombre' => 'Juego de Llaves Combinadas 12 piezas', 'precio_compra' => 25.00, 'precio_venta' => 39.99, 'stock_minimo' => 10, 'stock_maximo' => 50, 'categoria' => 2],
            ['codigo' => 'PROD-007', 'sku' => 'DES-001', 'nombre' => 'Desarmador de Precisión 7 piezas', 'precio_compra' => 8.00, 'precio_venta' => 14.99, 'stock_minimo' => 15, 'stock_maximo' => 80, 'categoria' => 2],
            ['codigo' => 'PROD-008', 'sku' => 'MAR-001', 'nombre' => 'Martillo de Carpintero 16 oz', 'precio_compra' => 6.50, 'precio_venta' => 12.99, 'stock_minimo' => 12, 'stock_maximo' => 60, 'categoria' => 2],
            ['codigo' => 'PROD-009', 'sku' => 'PIN-002', 'nombre' => 'Pinza de Presión 10"', 'precio_compra' => 5.00, 'precio_venta' => 9.99, 'stock_minimo' => 10, 'stock_maximo' => 50, 'categoria' => 2],
            ['codigo' => 'PROD-010', 'sku' => 'COR-001', 'nombre' => 'Cortador de Cerámica 40cm', 'precio_compra' => 18.00, 'precio_venta' => 29.99, 'stock_minimo' => 5, 'stock_maximo' => 25, 'categoria' => 2],
            
            // Materiales de Construcción (categoría 3)
            ['codigo' => 'PROD-011', 'sku' => 'CEM-001', 'nombre' => 'Cemento Gris 42.5 kg', 'precio_compra' => 7.50, 'precio_venta' => 11.50, 'stock_minimo' => 50, 'stock_maximo' => 300, 'categoria' => 3],
            ['codigo' => 'PROD-012', 'sku' => 'VAR-003', 'nombre' => 'Varilla Corrugada 3/8" x 6m', 'precio_compra' => 6.00, 'precio_venta' => 9.00, 'stock_minimo' => 40, 'stock_maximo' => 200, 'categoria' => 3],
            ['codigo' => 'PROD-013', 'sku' => 'BLO-001', 'nombre' => 'Block de Concreto 15x20x40', 'precio_compra' => 0.45, 'precio_venta' => 0.85, 'stock_minimo' => 200, 'stock_maximo' => 1000, 'categoria' => 3],
            ['codigo' => 'PROD-014', 'sku' => 'ARE-001', 'nombre' => 'Arena de Río (m3)', 'precio_compra' => 20.00, 'precio_venta' => 30.00, 'stock_minimo' => 20, 'stock_maximo' => 100, 'categoria' => 3],
            ['codigo' => 'PROD-015', 'sku' => 'GRA-001', 'nombre' => 'Grava Triturada (m3)', 'precio_compra' => 18.00, 'precio_venta' => 28.00, 'stock_minimo' => 15, 'stock_maximo' => 80, 'categoria' => 3],
            
            // Fertilizantes (categoría 4)
            ['codigo' => 'PROD-016', 'sku' => 'URE-001', 'nombre' => 'Urea Agrícola 46% (50kg)', 'precio_compra' => 35.00, 'precio_venta' => 49.99, 'stock_minimo' => 15, 'stock_maximo' => 80, 'categoria' => 4],
            ['codigo' => 'PROD-017', 'sku' => 'FOS-001', 'nombre' => 'Fosfato Diamónico DAP (50kg)', 'precio_compra' => 42.00, 'precio_venta' => 59.99, 'stock_minimo' => 10, 'stock_maximo' => 60, 'categoria' => 4],
            ['codigo' => 'PROD-018', 'sku' => 'NIT-001', 'nombre' => 'Nitrato de Amonio (50kg)', 'precio_compra' => 38.00, 'precio_venta' => 54.99, 'stock_minimo' => 10, 'stock_maximo' => 50, 'categoria' => 4],
            ['codigo' => 'PROD-019', 'sku' => 'FOL-001', 'nombre' => 'Fertilizante Foliar 20-20-20 (1kg)', 'precio_compra' => 8.00, 'precio_venta' => 14.99, 'stock_minimo' => 20, 'stock_maximo' => 100, 'categoria' => 4],
            ['codigo' => 'PROD-020', 'sku' => 'HER-001', 'nombre' => 'Herbicida Glifosato 1L', 'precio_compra' => 12.00, 'precio_venta' => 19.99, 'stock_minimo' => 15, 'stock_maximo' => 70, 'categoria' => 4],
            
            // Pinturas (categoría 5)
            ['codigo' => 'PROD-021', 'sku' => 'PNT-001', 'nombre' => 'Pintura Esmalte Negro 1 galón', 'precio_compra' => 12.00, 'precio_venta' => 19.99, 'stock_minimo' => 10, 'stock_maximo' => 60, 'categoria' => 5],
            ['codigo' => 'PROD-022', 'sku' => 'PNT-002', 'nombre' => 'Pintura Látex Blanco 1 galón', 'precio_compra' => 10.00, 'precio_venta' => 16.99, 'stock_minimo' => 15, 'stock_maximo' => 80, 'categoria' => 5],
            ['codigo' => 'PROD-023', 'sku' => 'PNT-003', 'nombre' => 'Pintura Vinílica (4 litros)', 'precio_compra' => 18.00, 'precio_venta' => 29.99, 'stock_minimo' => 8, 'stock_maximo' => 40, 'categoria' => 5],
            ['codigo' => 'PROD-024', 'sku' => 'THI-001', 'nombre' => 'Thinner para Pintura 1 litro', 'precio_compra' => 4.00, 'precio_venta' => 8.99, 'stock_minimo' => 20, 'stock_maximo' => 100, 'categoria' => 5],
            ['codigo' => 'PROD-025', 'sku' => 'PIN-003', 'nombre' => 'Pintura Epóxica 1 galón', 'precio_compra' => 25.00, 'precio_venta' => 39.99, 'stock_minimo' => 5, 'stock_maximo' => 30, 'categoria' => 5],
            
            // Ferretería General (categoría 6)
            ['codigo' => 'PROD-026', 'sku' => 'CLV-001', 'nombre' => 'Clavos de Acero 2" (libra)', 'precio_compra' => 1.20, 'precio_venta' => 2.49, 'stock_minimo' => 30, 'stock_maximo' => 200, 'categoria' => 6],
            ['codigo' => 'PROD-027', 'sku' => 'TOR-001', 'nombre' => 'Tornillos Autoperforantes 1" (100 uds)', 'precio_compra' => 3.00, 'precio_venta' => 5.99, 'stock_minimo' => 20, 'stock_maximo' => 120, 'categoria' => 6],
            ['codigo' => 'PROD-028', 'sku' => 'LUB-001', 'nombre' => 'Lubricante WD-40 300ml', 'precio_compra' => 3.50, 'precio_venta' => 6.99, 'stock_minimo' => 15, 'stock_maximo' => 80, 'categoria' => 6],
            ['codigo' => 'PROD-029', 'sku' => 'PEG-001', 'nombre' => 'Pegamento Instantáneo (3g)', 'precio_compra' => 1.50, 'precio_venta' => 3.49, 'stock_minimo' => 25, 'stock_maximo' => 150, 'categoria' => 6],
            ['codigo' => 'PROD-030', 'sku' => 'CIN-001', 'nombre' => 'Cinta Aislante Negra', 'precio_compra' => 0.80, 'precio_venta' => 1.99, 'stock_minimo' => 40, 'stock_maximo' => 200, 'categoria' => 6],
            
            // Jardinería (categoría 7)
            ['codigo' => 'PROD-031', 'sku' => 'MAN-001', 'nombre' => 'Manguera Jardín 50 pies', 'precio_compra' => 12.00, 'precio_venta' => 19.99, 'stock_minimo' => 8, 'stock_maximo' => 40, 'categoria' => 7],
            ['codigo' => 'PROD-032', 'sku' => 'ROC-001', 'nombre' => 'Rociador Manual 2 litros', 'precio_compra' => 5.00, 'precio_venta' => 9.99, 'stock_minimo' => 10, 'stock_maximo' => 50, 'categoria' => 7],
            ['codigo' => 'PROD-033', 'sku' => 'POD-001', 'nombre' => 'Podadora de Césped 3.5HP', 'precio_compra' => 120.00, 'precio_venta' => 189.99, 'stock_minimo' => 2, 'stock_maximo' => 15, 'categoria' => 7],
            ['codigo' => 'PROD-034', 'sku' => 'MOT-001', 'nombre' => 'Motosierra 18" 45cc', 'precio_compra' => 95.00, 'precio_venta' => 149.99, 'stock_minimo' => 3, 'stock_maximo' => 20, 'categoria' => 7],
            ['codigo' => 'PROD-035', 'sku' => 'PALA-001', 'nombre' => 'Pala de Jardín', 'precio_compra' => 6.00, 'precio_venta' => 11.99, 'stock_minimo' => 15, 'stock_maximo' => 60, 'categoria' => 7],
            
            // Seguridad Industrial (categoría 8)
            ['codigo' => 'PROD-036', 'sku' => 'CAS-001', 'nombre' => 'Casco de Seguridad Industrial', 'precio_compra' => 6.00, 'precio_venta' => 12.99, 'stock_minimo' => 15, 'stock_maximo' => 80, 'categoria' => 8],
            ['codigo' => 'PROD-037', 'sku' => 'GUA-001', 'nombre' => 'Guantes de Nitrilo (par)', 'precio_compra' => 2.50, 'precio_venta' => 5.49, 'stock_minimo' => 20, 'stock_maximo' => 120, 'categoria' => 8],
            ['codigo' => 'PROD-038', 'sku' => 'CHA-001', 'nombre' => 'Chaleco Reflectivo', 'precio_compra' => 4.00, 'precio_venta' => 8.99, 'stock_minimo' => 15, 'stock_maximo' => 70, 'categoria' => 8],
            ['codigo' => 'PROD-039', 'sku' => 'CON-001', 'nombre' => 'Cono de Seguridad 28"', 'precio_compra' => 8.00, 'precio_venta' => 14.99, 'stock_minimo' => 10, 'stock_maximo' => 50, 'categoria' => 8],
            ['codigo' => 'PROD-040', 'sku' => 'ARN-001', 'nombre' => 'Arnés de Seguridad', 'precio_compra' => 25.00, 'precio_venta' => 39.99, 'stock_minimo' => 5, 'stock_maximo' => 25, 'categoria' => 8],
        ];
        
        // Generar productos adicionales para llegar a 100
        $productos = [];
        
        foreach ($productosData as $data) {
            $categoriaId = $data['categoria'];
            unset($data['categoria']);
            
            $productos[] = Producto::create(array_merge($data, [
                'categoria_id' => $categoriaId,
                'proveedor_id' => rand(1, count($proveedores)),
                'unidad_medida' => 'unidad',
                'ubicacion_almacen' => 'A-' . str_pad(rand(1, 10), 2, '0', STR_PAD_LEFT) . '-' . str_pad(rand(1, 10), 2, '0', STR_PAD_LEFT),
                'estado' => 'activo',
            ]));
        }
        
        // Completar hasta 100 productos con datos aleatorios
        $categoriasIds = Categoria::pluck('id')->toArray(); 

        for ($i = 41; $i <= 100; $i++) {
            $categoriaId = $categoriasIds[array_rand($categoriasIds)];
            $precioCompra = rand(3, 150);
            $productos[] = Producto::create([
                'codigo' => 'PROD-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'sku' => 'SKU-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'nombre' => 'Producto ' . $i,
                'categoria_id' => $categoriaId,
                'proveedor_id' => rand(1, count($proveedores)),
                'unidad_medida' => 'unidad',
                'precio_compra' => $precioCompra,
                'precio_venta' => $precioCompra * 1.5,
                'stock_minimo' => rand(5, 30),
                'stock_maximo' => rand(50, 200),
                'ubicacion_almacen' => 'B-' . str_pad(rand(1, 10), 2, '0', STR_PAD_LEFT) . '-' . str_pad(rand(1, 10), 2, '0', STR_PAD_LEFT),
                'estado' => 'activo',
            ]);
        }

        // ═══════════════════════════════════════════════════════════════════
        // 6. INVENTARIO POR SUCURSAL (stocks variados)
        // ═══════════════════════════════════════════════════════════════════
        
        foreach ($productos as $producto) {
            foreach ($almacenes as $idx => $almacen) {
                // Crear stock variado por sucursal
                $stockBase = rand($producto->stock_minimo * 0.5, $producto->stock_maximo * 1.2);
                
                // Crear situaciones especiales según la sucursal
                if ($idx == 0) {
                    // Principal: Stock normal
                    $stock = rand($producto->stock_minimo, $producto->stock_maximo);
                } elseif ($idx == 1) {
                    // Santa Ana: SOBRESTOCK
                    $stock = rand($producto->stock_maximo, $producto->stock_maximo * 1.5);
                } elseif ($idx == 2) {
                    // San Miguel: STOCK BAJO
                    $stock = rand(0, $producto->stock_minimo);
                } else {
                    // Otras sucursales: variado
                    $stock = rand(0, $producto->stock_maximo * 1.2);
                }
                
                InventarioAlmacen::create([
                    'producto_id' => $producto->id,
                    'almacen_id' => $almacen->id,
                    'stock_actual' => $stock,
                    'stock_minimo' => $producto->stock_minimo,
                    'stock_maximo' => $producto->stock_maximo,
                    'punto_reorden' => $producto->stock_minimo * 0.8,
                ]);
            }
        }

        // ═══════════════════════════════════════════════════════════════════
        // 7. CLIENTES
        // ═══════════════════════════════════════════════════════════════════
        
        $clientesData = [
            ['codigo' => 'CLI-001', 'nombre' => 'Constructora Alas Doradas', 'tipo' => 'mayorista', 'limite_credito' => 25000, 'dias_credito' => 45],
            ['codigo' => 'CLI-002', 'nombre' => 'Agro Finca San Pablo', 'tipo' => 'mayorista', 'limite_credito' => 15000, 'dias_credito' => 30],
            ['codigo' => 'CLI-003', 'nombre' => 'Ferretería El Constructor', 'tipo' => 'minorista', 'limite_credito' => 5000, 'dias_credito' => 15],
            ['codigo' => 'CLI-004', 'nombre' => 'Distribuidora La Lima', 'tipo' => 'mayorista', 'limite_credito' => 20000, 'dias_credito' => 30],
            ['codigo' => 'CLI-005', 'nombre' => 'Comercial Ferretera S.A.', 'tipo' => 'mayorista', 'limite_credito' => 30000, 'dias_credito' => 45],
            ['codigo' => 'CLI-006', 'nombre' => 'Inversiones Martínez', 'tipo' => 'corporativo', 'limite_credito' => 100000, 'dias_credito' => 60],
            ['codigo' => 'CLI-007', 'nombre' => 'Ferretería Santa Ana', 'tipo' => 'minorista', 'limite_credito' => 8000, 'dias_credito' => 15],
            ['codigo' => 'CLI-008', 'nombre' => 'Agroservicios El Salvador', 'tipo' => 'mayorista', 'limite_credito' => 18000, 'dias_credito' => 30],
        ];
        
        foreach ($clientesData as $data) {
            Cliente::create(array_merge($data, [
                'email' => strtolower(str_replace(' ', '', $data['nombre'])) . '@correo.com',
                'telefono' => '2' . rand(2000, 2999) . '-' . rand(1000, 9999),
                'direccion_principal' => 'Dirección ' . $data['nombre'],
                'estado' => 'activo',
            ]));
        }

        // ═══════════════════════════════════════════════════════════════════
        // 8. TRANSPORTISTAS
        // ═══════════════════════════════════════════════════════════════════
        
        $transportistas = [
            ['codigo' => 'TRANS-001', 'nombre' => 'Transportes El Halcón', 'tipo' => 'externo', 'vehiculo_tipo' => 'camion', 'capacidad_kg' => 8000, 'tarifa_km' => 0.55],
            ['codigo' => 'TRANS-002', 'nombre' => 'Fletes Express', 'tipo' => 'externo', 'vehiculo_tipo' => 'furgon', 'capacidad_kg' => 1500, 'tarifa_km' => 0.35],
            ['codigo' => 'TRANS-003', 'nombre' => 'Transporte Propio (Central)', 'tipo' => 'propio', 'vehiculo_tipo' => 'camion', 'capacidad_kg' => 5000, 'tarifa_km' => 0.45],
            ['codigo' => 'TRANS-004', 'nombre' => 'Logística Rápida', 'tipo' => 'externo', 'vehiculo_tipo' => 'pickup', 'capacidad_kg' => 1000, 'tarifa_fija' => 40.00],
        ];
        
        foreach ($transportistas as $data) {
            Transportista::create(array_merge($data, [
                'vehiculo_placa' => strtoupper(Str::random(3)) . '-' . rand(100, 999),
                'conductor_nombre' => 'Conductor ' . $data['nombre'],
                'conductor_telefono' => '7' . rand(0000, 9999) . '-' . rand(0000, 9999),
                'estado' => 'disponible',
            ]));
        }

        $this->command->info('✅ TraceLog: Base de datos sembrada exitosamente.');
        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════════════════════');
        $this->command->info('👤 CREDENCIALES DE ACCESO');
        $this->command->info('═══════════════════════════════════════════════════════════');
        $this->command->info('🏢 Super Administrador: superadmin@tracelog.com / password');
        $this->command->info('');
        $this->command->info('🏪 SUCURSAL SAN SALVADOR:');
        $this->command->info('   Admin: admin.ss@tracelog.com / password');
        $this->command->info('   Cajero: cajero.ss@tracelog.com / password');
        $this->command->info('   Supervisor: bodega.ss@tracelog.com / password');
        $this->command->info('');
        $this->command->info('🏪 SUCURSAL SANTA ANA:');
        $this->command->info('   Admin: admin.sa@tracelog.com / password');
        $this->command->info('   Cajero: cajero.sa@tracelog.com / password');
        $this->command->info('   Supervisor: bodega.sa@tracelog.com / password');
        $this->command->info('');
        $this->command->info('🏪 SUCURSAL SAN MIGUEL:');
        $this->command->info('   Admin: admin.sm@tracelog.com / password');
        $this->command->info('   Cajero: cajero.sm@tracelog.com / password');
        $this->command->info('   Supervisor: bodega.sm@tracelog.com / password');
        $this->command->info('');
        $this->command->info('🏪 SUCURSAL LA LIBERTAD:');
        $this->command->info('   Admin: admin.ll@tracelog.com / password');
        $this->command->info('   Cajero: cajero.ll@tracelog.com / password');
        $this->command->info('   Supervisor: bodega.ll@tracelog.com / password');
        $this->command->info('');
        $this->command->info('🏪 SUCURSAL SONSONATE:');
        $this->command->info('   Admin: admin.so@tracelog.com / password');
        $this->command->info('   Cajero: cajero.so@tracelog.com / password');
        $this->command->info('   Supervisor: bodega.so@tracelog.com / password');
        $this->command->info('');
        $this->command->info('🏪 SUCURSAL USULUTÁN:');
        $this->command->info('   Admin: admin.us@tracelog.com / password');
        $this->command->info('   Cajero: cajero.us@tracelog.com / password');
        $this->command->info('   Supervisor: bodega.us@tracelog.com / password');
    }
}