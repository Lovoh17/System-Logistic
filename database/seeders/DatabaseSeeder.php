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

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ═══════════════════════════════════════════════════════════════════
        // 1. USUARIOS DEL SISTEMA
        // ═══════════════════════════════════════════════════════════════════
        
        User::updateOrCreate(
            ['email' => 'superadmin@tracelog.com'],
            ['name' => 'Super Administrador', 'password' => Hash::make('password')]
        );
        
        // Usuarios por sucursal
        $sucursales = ['ss', 'sa', 'sm', 'll', 'so', 'us'];
        $nombresSucursales = ['San Salvador', 'Santa Ana', 'San Miguel', 'La Libertad', 'Sonsonate', 'Usulután'];
        
        foreach ($sucursales as $index => $code) {
            User::updateOrCreate(
                ['email' => "admin.{$code}@tracelog.com"],
                ['name' => "Administrador {$nombresSucursales[$index]}", 'password' => Hash::make('password')]
            );
            User::updateOrCreate(
                ['email' => "cajero.{$code}@tracelog.com"],
                ['name' => "Cajero {$nombresSucursales[$index]}", 'password' => Hash::make('password')]
            );
            User::updateOrCreate(
                ['email' => "bodega.{$code}@tracelog.com"],
                ['name' => "Supervisor Bodega {$nombresSucursales[$index]}", 'password' => Hash::make('password')]
            );
        }
        
        User::updateOrCreate(
            ['email' => 'logistica@tracelog.com'],
            ['name' => 'Coordinador Logístico', 'password' => Hash::make('password')]
        );
        
        // ═══════════════════════════════════════════════════════════════════
        // 2. CATEGORÍAS
        // ═══════════════════════════════════════════════════════════════════
        
        $categorias = [
            ['nombre' => 'Herramientas Eléctricas', 'slug' => 'herramientas-electricas'],
            ['nombre' => 'Herramientas Manuales', 'slug' => 'herramientas-manuales'],
            ['nombre' => 'Materiales de Construcción', 'slug' => 'materiales-construccion'],
            ['nombre' => 'Fertilizantes', 'slug' => 'fertilizantes'],
            ['nombre' => 'Pinturas', 'slug' => 'pinturas'],
            ['nombre' => 'Ferretería General', 'slug' => 'ferreteria-general'],
            ['nombre' => 'Jardinería', 'slug' => 'jardineria'],
            ['nombre' => 'Seguridad Industrial', 'slug' => 'seguridad'],
        ];
        
        $categoriasCreadas = [];
        foreach ($categorias as $cat) {
            $categoriasCreadas[] = Categoria::create($cat);
        }
        
        // ═══════════════════════════════════════════════════════════════════
        // 3. PROVEEDORES
        // ═══════════════════════════════════════════════════════════════════
        
        $proveedor1 = Proveedor::create([
            'codigo' => 'PROV-001',
            'nombre' => 'Truper El Salvador',
            'email' => 'ventas@truper.com.sv',
            'telefono' => '2244-1111',
            'pais' => 'El Salvador',
            'estado' => 'activo',
        ]);
        
        $proveedor2 = Proveedor::create([
            'codigo' => 'PROV-002',
            'nombre' => 'Pinturas Comex',
            'email' => 'pedidos@comex.com',
            'telefono' => '2222-2222',
            'pais' => 'El Salvador',
            'estado' => 'activo',
        ]);
        
        $proveedor3 = Proveedor::create([
            'codigo' => 'PROV-003',
            'nombre' => 'Distribuidora La Selecta',
            'email' => 'ventas@laselecta.com.sv',
            'telefono' => '2222-3333',
            'pais' => 'El Salvador',
            'estado' => 'activo',
        ]);
        
        // ═══════════════════════════════════════════════════════════════════
        // 4. ALMACENES (6 SUCURSALES)
        // ═══════════════════════════════════════════════════════════════════
        
        $almacenesData = [
            ['codigo' => 'ALM-001', 'nombre' => 'San Salvador (Central)', 'es_principal' => true, 'activo' => true],
            ['codigo' => 'ALM-002', 'nombre' => 'Santa Ana', 'es_principal' => false, 'activo' => true],
            ['codigo' => 'ALM-003', 'nombre' => 'San Miguel', 'es_principal' => false, 'activo' => true],
            ['codigo' => 'ALM-004', 'nombre' => 'La Libertad', 'es_principal' => false, 'activo' => true],
            ['codigo' => 'ALM-005', 'nombre' => 'Sonsonate', 'es_principal' => false, 'activo' => true],
            ['codigo' => 'ALM-006', 'nombre' => 'Usulután', 'es_principal' => false, 'activo' => true],
        ];
        
        $almacenes = [];
        foreach ($almacenesData as $data) {
            $almacenes[] = Almacen::create($data);
        }
        
        // ═══════════════════════════════════════════════════════════════════
        // 5. PRODUCTOS CON STOCK REAL
        // ═══════════════════════════════════════════════════════════════════
        
        $productosLista = [
            ['codigo' => 'PROD-001', 'nombre' => 'Taladro Percutor 1/2" 650W', 'precio_compra' => 45.00, 'precio_venta' => 69.99, 'stock' => 25, 'min' => 5, 'max' => 30, 'categoria' => 1],
            ['codigo' => 'PROD-002', 'nombre' => 'Rotomartillo SDS Plus 800W', 'precio_compra' => 85.00, 'precio_venta' => 129.99, 'stock' => 15, 'min' => 3, 'max' => 20, 'categoria' => 1],
            ['codigo' => 'PROD-003', 'nombre' => 'Esmeril Angular 4-1/2" 850W', 'precio_compra' => 32.00, 'precio_venta' => 49.99, 'stock' => 35, 'min' => 8, 'max' => 40, 'categoria' => 1],
            ['codigo' => 'PROD-004', 'nombre' => 'Sierra Caladora 550W', 'precio_compra' => 38.00, 'precio_venta' => 59.99, 'stock' => 20, 'min' => 4, 'max' => 25, 'categoria' => 1],
            ['codigo' => 'PROD-005', 'nombre' => 'Juego de Llaves Combinadas 12pz', 'precio_compra' => 25.00, 'precio_venta' => 39.99, 'stock' => 45, 'min' => 10, 'max' => 50, 'categoria' => 2],
            ['codigo' => 'PROD-006', 'nombre' => 'Desarmador de Precisión 7pz', 'precio_compra' => 8.00, 'precio_venta' => 14.99, 'stock' => 70, 'min' => 15, 'max' => 80, 'categoria' => 2],
            ['codigo' => 'PROD-007', 'nombre' => 'Martillo de Carpintero 16 oz', 'precio_compra' => 6.50, 'precio_venta' => 12.99, 'stock' => 50, 'min' => 12, 'max' => 60, 'categoria' => 2],
            ['codigo' => 'PROD-008', 'nombre' => 'Pinza de Presión 10"', 'precio_compra' => 5.00, 'precio_venta' => 9.99, 'stock' => 40, 'min' => 10, 'max' => 50, 'categoria' => 2],
            ['codigo' => 'PROD-009', 'nombre' => 'Cemento Gris 42.5 kg', 'precio_compra' => 7.50, 'precio_venta' => 11.50, 'stock' => 250, 'min' => 50, 'max' => 300, 'categoria' => 3],
            ['codigo' => 'PROD-010', 'nombre' => 'Varilla Corrugada 3/8" x 6m', 'precio_compra' => 6.00, 'precio_venta' => 9.00, 'stock' => 150, 'min' => 40, 'max' => 200, 'categoria' => 3],
            ['codigo' => 'PROD-011', 'nombre' => 'Block de Concreto 15x20x40', 'precio_compra' => 0.45, 'precio_venta' => 0.85, 'stock' => 800, 'min' => 200, 'max' => 1000, 'categoria' => 3],
            ['codigo' => 'PROD-012', 'nombre' => 'Urea Agrícola 46% (50kg)', 'precio_compra' => 35.00, 'precio_venta' => 49.99, 'stock' => 60, 'min' => 15, 'max' => 80, 'categoria' => 4],
            ['codigo' => 'PROD-013', 'nombre' => 'Fosfato Diamónico DAP (50kg)', 'precio_compra' => 42.00, 'precio_venta' => 59.99, 'stock' => 45, 'min' => 10, 'max' => 60, 'categoria' => 4],
            ['codigo' => 'PROD-014', 'nombre' => 'Fertilizante Foliar 20-20-20', 'precio_compra' => 8.00, 'precio_venta' => 14.99, 'stock' => 85, 'min' => 20, 'max' => 100, 'categoria' => 4],
            ['codigo' => 'PROD-015', 'nombre' => 'Pintura Esmalte Negro 1 galón', 'precio_compra' => 12.00, 'precio_venta' => 19.99, 'stock' => 50, 'min' => 10, 'max' => 60, 'categoria' => 5],
            ['codigo' => 'PROD-016', 'nombre' => 'Pintura Látex Blanco 1 galón', 'precio_compra' => 10.00, 'precio_venta' => 16.99, 'stock' => 65, 'min' => 15, 'max' => 80, 'categoria' => 5],
            ['codigo' => 'PROD-017', 'nombre' => 'Thinner para Pintura 1 litro', 'precio_compra' => 4.00, 'precio_venta' => 8.99, 'stock' => 90, 'min' => 20, 'max' => 100, 'categoria' => 5],
            ['codigo' => 'PROD-018', 'nombre' => 'Clavos de Acero 2" (libra)', 'precio_compra' => 1.20, 'precio_venta' => 2.49, 'stock' => 180, 'min' => 30, 'max' => 200, 'categoria' => 6],
            ['codigo' => 'PROD-019', 'nombre' => 'Tornillos Autoperforantes 1"', 'precio_compra' => 3.00, 'precio_venta' => 5.99, 'stock' => 100, 'min' => 20, 'max' => 120, 'categoria' => 6],
            ['codigo' => 'PROD-020', 'nombre' => 'Lubricante WD-40 300ml', 'precio_compra' => 3.50, 'precio_venta' => 6.99, 'stock' => 70, 'min' => 15, 'max' => 80, 'categoria' => 6],
            ['codigo' => 'PROD-021', 'nombre' => 'Taladro de Impacto 20V', 'precio_compra' => 95.00, 'precio_venta' => 149.99, 'stock' => 12, 'min' => 3, 'max' => 25, 'categoria' => 1],
            ['codigo' => 'PROD-022', 'nombre' => 'Sierra Circular 7-1/4"', 'precio_compra' => 75.00, 'precio_venta' => 119.99, 'stock' => 18, 'min' => 4, 'max' => 30, 'categoria' => 1],
            ['codigo' => 'PROD-023', 'nombre' => 'Cinta Métrica 5m', 'precio_compra' => 2.50, 'precio_venta' => 4.99, 'stock' => 120, 'min' => 30, 'max' => 150, 'categoria' => 2],
            ['codigo' => 'PROD-024', 'nombre' => 'Nivel de Mano 60cm', 'precio_compra' => 8.00, 'precio_venta' => 14.99, 'stock' => 35, 'min' => 10, 'max' => 50, 'categoria' => 2],
            ['codigo' => 'PROD-025', 'nombre' => 'Calcomanía Reflectiva', 'precio_compra' => 1.00, 'precio_venta' => 2.50, 'stock' => 500, 'min' => 100, 'max' => 600, 'categoria' => 8],
            ['codigo' => 'PROD-026', 'nombre' => 'Guantes de Seguridad', 'precio_compra' => 3.00, 'precio_venta' => 5.99, 'stock' => 80, 'min' => 20, 'max' => 100, 'categoria' => 8],
            ['codigo' => 'PROD-027', 'nombre' => 'Casco de Seguridad Industrial', 'precio_compra' => 6.00, 'precio_venta' => 12.99, 'stock' => 45, 'min' => 10, 'max' => 60, 'categoria' => 8],
            ['codigo' => 'PROD-028', 'nombre' => 'Machete para Jardín', 'precio_compra' => 8.00, 'precio_venta' => 15.99, 'stock' => 30, 'min' => 8, 'max' => 40, 'categoria' => 7],
            ['codigo' => 'PROD-029', 'nombre' => 'Rastrillo de Jardín', 'precio_compra' => 7.50, 'precio_venta' => 13.99, 'stock' => 25, 'min' => 6, 'max' => 35, 'categoria' => 7],
            ['codigo' => 'PROD-030', 'nombre' => 'Manguera de Jardín 50 pies', 'precio_compra' => 12.00, 'precio_venta' => 19.99, 'stock' => 20, 'min' => 5, 'max' => 30, 'categoria' => 7],
        ];
        
        $productos = [];
        foreach ($productosLista as $data) {
            $producto = Producto::create([
                'codigo' => $data['codigo'],
                'nombre' => $data['nombre'],
                'categoria_id' => $data['categoria'],
                'proveedor_id' => $proveedor1->id,
                'unidad_medida' => 'unidad',
                'precio_compra' => $data['precio_compra'],
                'precio_venta' => $data['precio_venta'],
                'stock_actual' => $data['stock'],
                'stock_minimo' => $data['min'],
                'stock_maximo' => $data['max'],
                'ubicacion_almacen' => 'A-' . str_pad(rand(1, 10), 2, '0', STR_PAD_LEFT),
                'estado' => 'activo',
            ]);
            $productos[] = $producto;
        }
        
        // ═══════════════════════════════════════════════════════════════════
        // 6. INVENTARIO POR SUCURSAL (distribuir stock)
        // ═══════════════════════════════════════════════════════════════════
        
        foreach ($productos as $producto) {
            // Buscar el almacén principal
            $principal = Almacen::where('es_principal', true)->first();
            
            // Asignar 60% del stock al almacén principal
            $stockPrincipal = round($producto->stock_actual * 0.6);
            
            // Repartir el 40% restante entre las otras 5 sucursales
            $stockRestante = $producto->stock_actual - $stockPrincipal;
            $stockPorSucursal = round($stockRestante / 5);
            
            foreach ($almacenes as $almacen) {
                if ($almacen->es_principal) {
                    $stock = $stockPrincipal;
                } else {
                    $stock = $stockPorSucursal;
                }
                
                InventarioAlmacen::create([
                    'producto_id' => $producto->id,
                    'almacen_id' => $almacen->id,
                    'stock_actual' => $stock,
                    'stock_minimo' => $producto->stock_minimo,
                    'stock_maximo' => $producto->stock_maximo,
                    'punto_reorden' => round($producto->stock_minimo * 0.8),
                ]);
            }
        }
        
        // ═══════════════════════════════════════════════════════════════════
        // 7. CLIENTES
        // ═══════════════════════════════════════════════════════════════════
        
        $clientes = [
            ['codigo' => 'CLI-001', 'nombre' => 'Supermercado El Colono', 'tipo' => 'mayorista', 'limite_credito' => 15000, 'email' => 'compras@elcolono.com', 'telefono' => '2333-4444'],
            ['codigo' => 'CLI-002', 'nombre' => 'Farmacia San Nicolás', 'tipo' => 'minorista', 'limite_credito' => 5000, 'email' => 'pedidos@fsn.com', 'telefono' => '2211-9988'],
            ['codigo' => 'CLI-003', 'nombre' => 'Hotel Sheraton Presidente', 'tipo' => 'corporativo', 'limite_credito' => 50000, 'email' => 'compras@sheraton.com', 'telefono' => '2283-4000'],
            ['codigo' => 'CLI-004', 'nombre' => 'Constructora Alas Doradas', 'tipo' => 'mayorista', 'limite_credito' => 25000, 'email' => 'compras@alasdoradas.com', 'telefono' => '2255-1234'],
            ['codigo' => 'CLI-005', 'nombre' => 'Agro Finca San Pablo', 'tipo' => 'mayorista', 'limite_credito' => 18000, 'email' => 'ventas@agrofinca.com', 'telefono' => '2444-5678'],
            ['codigo' => 'CLI-006', 'nombre' => 'Restaurante La Pampa', 'tipo' => 'minorista', 'limite_credito' => 8000, 'email' => 'pedidos@lapampa.com', 'telefono' => '2278-1234'],
            ['codigo' => 'CLI-007', 'nombre' => 'Ferretería El Constructor', 'tipo' => 'minorista', 'limite_credito' => 5000, 'email' => 'ventas@elconstructor.com', 'telefono' => '2222-7777'],
            ['codigo' => 'CLI-008', 'nombre' => 'Distribuidora La Lima', 'tipo' => 'mayorista', 'limite_credito' => 20000, 'email' => 'compras@lalima.com', 'telefono' => '2333-8888'],
        ];
        
        foreach ($clientes as $data) {
            Cliente::create(array_merge($data, [
                'estado' => 'activo',
                'direccion_principal' => 'Dirección ' . $data['nombre'],
            ]));
        }
        
        // ═══════════════════════════════════════════════════════════════════
        // 8. TRANSPORTISTAS
        // ═══════════════════════════════════════════════════════════════════
        
        $transportistas = [
            ['codigo' => 'TRANS-001', 'nombre' => 'Transportes El Halcón', 'tipo' => 'externo', 'vehiculo_tipo' => 'camion', 'capacidad_kg' => 8000, 'estado' => 'disponible'],
            ['codigo' => 'TRANS-002', 'nombre' => 'Fletes Express', 'tipo' => 'externo', 'vehiculo_tipo' => 'pickup', 'capacidad_kg' => 1500, 'estado' => 'disponible'],
            ['codigo' => 'TRANS-003', 'nombre' => 'Transporte Propio Central', 'tipo' => 'propio', 'vehiculo_tipo' => 'camion', 'capacidad_kg' => 5000, 'estado' => 'disponible'],
        ];
        
        foreach ($transportistas as $data) {
            Transportista::create($data);
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
        $this->command->info('');
        $this->command->info('📧 Coordinador Logístico: logistica@tracelog.com / password');
    }
}