<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Proveedor;
use App\Models\Cliente;
use App\Models\DireccionCliente;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Transportista;
use App\Models\Almacen;
use App\Models\InventarioAlmacen;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ═══════════════════════════════════════════════════════════════════
        // 1. ROLES SPATIE
        // ═══════════════════════════════════════════════════════════════════

        $roles = [
            'super_admin'       => 'Super Administrador — acceso total al sistema',
            'admin_sucursal'    => 'Administrador de sucursal — gestión de su almacén asignado',
            'cajero'            => 'Cajero — alta/consulta de pedidos de venta',
            'supervisor_bodega' => 'Supervisor de bodega — gestión de inventario y traslados',
            'logistica'         => 'Logística — gestión de envíos y transportistas',
        ];

        $rolesCreados = [];
        foreach ($roles as $nombre => $descripcion) {
            $rolesCreados[$nombre] = Role::firstOrCreate(
                ['name' => $nombre, 'guard_name' => 'web']
            );
        }

        // Permisos básicos por módulo
        $permisos = [
            'usuarios.ver', 'usuarios.crear', 'usuarios.editar', 'usuarios.eliminar',
            'productos.ver', 'productos.crear', 'productos.editar', 'productos.eliminar',
            'inventario.ver', 'inventario.ajustar', 'inventario.traslados',
            'compras.ver', 'compras.crear', 'compras.aprobar', 'compras.cancelar',
            'ventas.ver', 'ventas.crear', 'ventas.aprobar', 'ventas.cancelar',
            'envios.ver', 'envios.crear', 'envios.despachar', 'envios.gestionar',
            'reportes.ver', 'reportes.exportar',
            'config.ver', 'config.editar',
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'web']);
        }

        // Asignar permisos a roles
        $rolesCreados['super_admin']->syncPermissions(Permission::all());

        $rolesCreados['admin_sucursal']->syncPermissions([
            'productos.ver', 'productos.crear', 'productos.editar',
            'inventario.ver', 'inventario.ajustar', 'inventario.traslados',
            'compras.ver', 'compras.crear', 'compras.aprobar',
            'ventas.ver', 'ventas.crear', 'ventas.aprobar', 'ventas.cancelar',
            'envios.ver', 'envios.crear', 'envios.despachar',
            'reportes.ver', 'reportes.exportar',
        ]);

        $rolesCreados['cajero']->syncPermissions([
            'productos.ver',
            'inventario.ver',
            'ventas.ver', 'ventas.crear',
            'envios.ver',
            'reportes.ver',
        ]);

        $rolesCreados['supervisor_bodega']->syncPermissions([
            'productos.ver', 'productos.editar',
            'inventario.ver', 'inventario.ajustar', 'inventario.traslados',
            'compras.ver', 'compras.crear',
            'ventas.ver',
            'envios.ver',
            'reportes.ver',
        ]);

        $rolesCreados['logistica']->syncPermissions([
            'productos.ver',
            'inventario.ver',
            'ventas.ver',
            'envios.ver', 'envios.crear', 'envios.despachar', 'envios.gestionar',
            'reportes.ver', 'reportes.exportar',
        ]);

        // ═══════════════════════════════════════════════════════════════════
        // 2. ALMACENES
        // ═══════════════════════════════════════════════════════════════════

        $almacenesData = [
            ['codigo' => 'ALM-001', 'nombre' => 'San Salvador (Central)', 'es_principal' => true,  'activo' => true, 'responsable' => 'Carlos Martínez', 'telefono' => '2233-4455', 'direccion' => 'Centro Comercial Galerías, local 15'],
            ['codigo' => 'ALM-002', 'nombre' => 'Santa Ana',              'es_principal' => false, 'activo' => true, 'responsable' => 'Ana López', 'telefono' => '2441-5566', 'direccion' => '2a Calle Poniente #23'],
            ['codigo' => 'ALM-003', 'nombre' => 'San Miguel',             'es_principal' => false, 'activo' => true, 'responsable' => 'José Hernández', 'telefono' => '2661-7788', 'direccion' => 'Colonia Santa Mónica, bloque 3'],
            ['codigo' => 'ALM-004', 'nombre' => 'La Libertad',            'es_principal' => false, 'activo' => true, 'responsable' => 'María García', 'telefono' => '2312-3344', 'direccion' => 'Puerto La Libertad, local 8'],
            ['codigo' => 'ALM-005', 'nombre' => 'Sonsonate',              'es_principal' => false, 'activo' => true, 'responsable' => 'Pedro Rivas', 'telefono' => '2451-6677', 'direccion' => 'Barrio El Centro, 3a Av. Norte'],
            ['codigo' => 'ALM-006', 'nombre' => 'Usulután',               'es_principal' => false, 'activo' => true, 'responsable' => 'Laura Díaz', 'telefono' => '2671-8899', 'direccion' => 'Calle Principal, frente al parque'],
        ];

        $almacenes = [];
        $almacenesPorCodigo = [];
        foreach ($almacenesData as $data) {
            $almacen = Almacen::create($data);
            $almacenes[] = $almacen;
            $almacenesPorCodigo[$data['codigo']] = $almacen;
        }

        // ═══════════════════════════════════════════════════════════════════
        // 3. USUARIOS
        // ═══════════════════════════════════════════════════════════════════

        // Super administrador
        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@tracelog.com'],
            [
                'name' => 'Super Administrador',
                'password' => Hash::make('password'),
                'almacen_id' => null,
                'email_verified_at' => now(),
            ]
        );
        $superAdmin->assignRole('super_admin');

        // Usuarios por sucursal
        $sucursalMap = [
            'ss' => ['nombre' => 'San Salvador', 'almacen' => $almacenesPorCodigo['ALM-001']],
            'sa' => ['nombre' => 'Santa Ana',    'almacen' => $almacenesPorCodigo['ALM-002']],
            'sm' => ['nombre' => 'San Miguel',   'almacen' => $almacenesPorCodigo['ALM-003']],
            'll' => ['nombre' => 'La Libertad',  'almacen' => $almacenesPorCodigo['ALM-004']],
            'so' => ['nombre' => 'Sonsonate',    'almacen' => $almacenesPorCodigo['ALM-005']],
            'us' => ['nombre' => 'Usulután',     'almacen' => $almacenesPorCodigo['ALM-006']],
        ];

        foreach ($sucursalMap as $code => $info) {
            $almacenId = $info['almacen']->id;
            $nombre    = $info['nombre'];

            $admin = User::updateOrCreate(
                ['email' => "admin.{$code}@tracelog.com"],
                [
                    'name' => "Administrador {$nombre}",
                    'password' => Hash::make('password'),
                    'almacen_id' => $almacenId,
                    'email_verified_at' => now(),
                ]
            );
            $admin->assignRole('admin_sucursal');

            $cajero = User::updateOrCreate(
                ['email' => "cajero.{$code}@tracelog.com"],
                [
                    'name' => "Cajero {$nombre}",
                    'password' => Hash::make('password'),
                    'almacen_id' => $almacenId,
                    'email_verified_at' => now(),
                ]
            );
            $cajero->assignRole('cajero');

            $bodega = User::updateOrCreate(
                ['email' => "bodega.{$code}@tracelog.com"],
                [
                    'name' => "Supervisor Bodega {$nombre}",
                    'password' => Hash::make('password'),
                    'almacen_id' => $almacenId,
                    'email_verified_at' => now(),
                ]
            );
            $bodega->assignRole('supervisor_bodega');
        }

        $logistica = User::updateOrCreate(
            ['email' => 'logistica@tracelog.com'],
            [
                'name' => 'Coordinador Logístico',
                'password' => Hash::make('password'),
                'almacen_id' => null,
                'email_verified_at' => now(),
            ]
        );
        $logistica->assignRole('logistica');

        // ═══════════════════════════════════════════════════════════════════
        // 4. CATEGORÍAS
        // ═══════════════════════════════════════════════════════════════════

        $categoriasData = [
            ['nombre' => 'Herramientas Eléctricas', 'slug' => 'herramientas-electricas', 'icono' => 'bolt', 'color' => '#F59E0B', 'activo' => true],
            ['nombre' => 'Herramientas Manuales',   'slug' => 'herramientas-manuales',   'icono' => 'wrench', 'color' => '#6B7280', 'activo' => true],
            ['nombre' => 'Materiales de Construcción', 'slug' => 'materiales-construccion', 'icono' => 'building', 'color' => '#78716C', 'activo' => true],
            ['nombre' => 'Fertilizantes',           'slug' => 'fertilizantes',           'icono' => 'leaf', 'color' => '#22C55E', 'activo' => true],
            ['nombre' => 'Pinturas',                'slug' => 'pinturas',                'icono' => 'paint', 'color' => '#3B82F6', 'activo' => true],
            ['nombre' => 'Ferretería General',      'slug' => 'ferreteria-general',      'icono' => 'cog', 'color' => '#8B5CF6', 'activo' => true],
            ['nombre' => 'Jardinería',              'slug' => 'jardineria',              'icono' => 'flower', 'color' => '#10B981', 'activo' => true],
            ['nombre' => 'Seguridad Industrial',    'slug' => 'seguridad',               'icono' => 'shield', 'color' => '#EF4444', 'activo' => true],
        ];

        $categorias = [];
        foreach ($categoriasData as $cat) {
            $categorias[] = Categoria::create($cat);
        }

        // ═══════════════════════════════════════════════════════════════════
        // 5. PROVEEDORES
        // ═══════════════════════════════════════════════════════════════════

        $proveedor1 = Proveedor::create([
            'codigo'             => 'PROV-001',
            'nombre'             => 'Truper El Salvador',
            'razon_social'       => 'Truper SA de CV',
            'ruc'                => '0614-123456-001-0',
            'email'              => 'ventas@truper.com.sv',
            'telefono'           => '2244-1111',
            'contacto_nombre'    => 'Carlos Mendoza',
            'contacto_email'     => 'carlos.mendoza@truper.com',
            'contacto_telefono'  => '7744-1122',
            'pais'               => 'El Salvador',
            'departamento'       => 'San Salvador',
            'municipio'          => 'San Salvador',
            'direccion'          => 'Carretera a Santa Tecla, km 10.5',
            'categoria'          => 'general',
            'tiempo_entrega_dias'=> 3,
            'calificacion'       => 4.8,
            'estado'             => 'activo',
        ]);

        $proveedor2 = Proveedor::create([
            'codigo'             => 'PROV-002',
            'nombre'             => 'Pinturas Comex',
            'razon_social'       => 'Comex El Salvador',
            'ruc'                => '0614-654321-002-0',
            'email'              => 'pedidos@comex.com',
            'telefono'           => '2222-2222',
            'contacto_nombre'    => 'Ana Rivera',
            'contacto_email'     => 'ana.rivera@comex.com',
            'contacto_telefono'  => '7766-3344',
            'pais'               => 'El Salvador',
            'departamento'       => 'Santa Ana',
            'municipio'          => 'Santa Ana',
            'direccion'          => '3a Calle Poniente #45',
            'categoria'          => 'general',
            'tiempo_entrega_dias'=> 5,
            'calificacion'       => 4.5,
            'estado'             => 'activo',
        ]);

        $proveedor3 = Proveedor::create([
            'codigo'             => 'PROV-003',
            'nombre'             => 'Distribuidora La Selecta',
            'razon_social'       => 'La Selecta Distribuciones',
            'ruc'                => '0614-987654-003-0',
            'email'              => 'ventas@laselecta.com.sv',
            'telefono'           => '2222-3333',
            'contacto_nombre'    => 'Roberto Gómez',
            'contacto_email'     => 'roberto.gomez@laselecta.com',
            'contacto_telefono'  => '7788-5566',
            'pais'               => 'El Salvador',
            'departamento'       => 'San Salvador',
            'municipio'          => 'Soyapango',
            'direccion'          => 'Polígono Industrial Don Bosco',
            'categoria'          => 'materia_prima',
            'tiempo_entrega_dias'=> 2,
            'calificacion'       => 4.9,
            'estado'             => 'activo',
        ]);

        // ═══════════════════════════════════════════════════════════════════
        // 6. PRODUCTOS
        // ═══════════════════════════════════════════════════════════════════

        $productosLista = [
            // [ codigo, nombre, precio_compra, precio_venta, stock_total, min, max, cat_idx, proveedor ]
            ['PROD-001', 'Taladro Percutor 1/2" 650W',        45.00,  69.99,  25,  5,  30,  0, $proveedor1],
            ['PROD-002', 'Rotomartillo SDS Plus 800W',         85.00, 129.99,  15,  3,  20,  0, $proveedor1],
            ['PROD-003', 'Esmeril Angular 4-1/2" 850W',        32.00,  49.99,  35,  8,  40,  0, $proveedor1],
            ['PROD-004', 'Sierra Caladora 550W',               38.00,  59.99,  20,  4,  25,  0, $proveedor1],
            ['PROD-005', 'Juego de Llaves Combinadas 12pz',    25.00,  39.99,  45, 10,  50,  1, $proveedor1],
            ['PROD-006', 'Desarmador de Precisión 7pz',         8.00,  14.99,  70, 15,  80,  1, $proveedor1],
            ['PROD-007', 'Martillo de Carpintero 16 oz',        6.50,  12.99,  50, 12,  60,  1, $proveedor1],
            ['PROD-008', 'Pinza de Presión 10"',                5.00,   9.99,  40, 10,  50,  1, $proveedor1],
            ['PROD-009', 'Cemento Gris 42.5 kg',                7.50,  11.50, 250, 50, 300,  2, $proveedor3],
            ['PROD-010', 'Varilla Corrugada 3/8" x 6m',         6.00,   9.00, 150, 40, 200,  2, $proveedor3],
            ['PROD-011', 'Block de Concreto 15x20x40',          0.45,   0.85, 800,200,1000,  2, $proveedor3],
            ['PROD-012', 'Urea Agrícola 46% (50kg)',            35.00,  49.99,  60, 15,  80,  3, $proveedor3],
            ['PROD-013', 'Fosfato Diamónico DAP (50kg)',        42.00,  59.99,  45, 10,  60,  3, $proveedor3],
            ['PROD-014', 'Fertilizante Foliar 20-20-20',         8.00,  14.99,  85, 20, 100,  3, $proveedor3],
            ['PROD-015', 'Pintura Esmalte Negro 1 galón',       12.00,  19.99,  50, 10,  60,  4, $proveedor2],
            ['PROD-016', 'Pintura Látex Blanco 1 galón',        10.00,  16.99,  65, 15,  80,  4, $proveedor2],
            ['PROD-017', 'Thinner para Pintura 1 litro',         4.00,   8.99,  90, 20, 100,  4, $proveedor2],
            ['PROD-018', 'Clavos de Acero 2" (libra)',           1.20,   2.49, 180, 30, 200,  5, $proveedor1],
            ['PROD-019', 'Tornillos Autoperforantes 1"',         3.00,   5.99, 100, 20, 120,  5, $proveedor1],
            ['PROD-020', 'Lubricante WD-40 300ml',               3.50,   6.99,  70, 15,  80,  5, $proveedor1],
            ['PROD-021', 'Taladro de Impacto 20V',              95.00, 149.99,  12,  3,  25,  0, $proveedor1],
            ['PROD-022', 'Sierra Circular 7-1/4"',              75.00, 119.99,  18,  4,  30,  0, $proveedor1],
            ['PROD-023', 'Cinta Métrica 5m',                     2.50,   4.99, 120, 30, 150,  1, $proveedor1],
            ['PROD-024', 'Nivel de Mano 60cm',                   8.00,  14.99,  35, 10,  50,  1, $proveedor1],
            ['PROD-025', 'Calcomanía Reflectiva',                1.00,   2.50, 500,100, 600,  7, $proveedor1],
            ['PROD-026', 'Guantes de Seguridad',                 3.00,   5.99,  80, 20, 100,  7, $proveedor1],
            ['PROD-027', 'Casco de Seguridad Industrial',        6.00,  12.99,  45, 10,  60,  7, $proveedor1],
            ['PROD-028', 'Machete para Jardín',                  8.00,  15.99,  30,  8,  40,  6, $proveedor1],
            ['PROD-029', 'Rastrillo de Jardín',                  7.50,  13.99,  25,  6,  35,  6, $proveedor1],
            ['PROD-030', 'Manguera de Jardín 50 pies',          12.00,  19.99,  20,  5,  30,  6, $proveedor1],
        ];

        $productos = [];
        foreach ($productosLista as $item) {
            [$codigo, $nombre, $precioC, $precioV, $stockTotal, $min, $max, $catIdx, $proveedor] = $item;
            
            $producto = Producto::create([
                'codigo'           => $codigo,
                'nombre'           => $nombre,
                'sku'              => 'SKU-' . $codigo,
                'categoria_id'     => $categorias[$catIdx]->id,
                'proveedor_id'     => $proveedor->id,
                'unidad_medida'    => 'unidad',
                'precio_compra'    => $precioC,
                'precio_venta'     => $precioV,
                'ubicacion_almacen'=> 'A-' . str_pad(rand(1, 10), 2, '0', STR_PAD_LEFT),
                'estado'           => 'activo',
            ]);

            $productos[] = compact('producto', 'stockTotal', 'min', 'max');
        }

        // ═══════════════════════════════════════════════════════════════════
        // 7. INVENTARIO POR SUCURSAL
        // ═══════════════════════════════════════════════════════════════════

        $totalAlmacenes = count($almacenes);
        $almacenesSecundarios = array_filter($almacenes, fn($a) => !$a->es_principal);
        $numSecundarios = count($almacenesSecundarios);

        foreach ($productos as $data) {
            $producto = $data['producto'];
            $stockTotal = $data['stockTotal'];
            $min = $data['min'];
            $max = $data['max'];

            // 60% al almacén principal
            $stockPrincipal = (int) round($stockTotal * 0.6);
            
            // El resto se distribuye entre los almacenes secundarios
            $stockRestante = $stockTotal - $stockPrincipal;
            $stockPorSecundario = $numSecundarios > 0 ? (int) round($stockRestante / $numSecundarios) : 0;

            foreach ($almacenes as $almacen) {
                if ($almacen->es_principal) {
                    $stock = $stockPrincipal;
                } else {
                    $stock = $stockPorSecundario;
                }

                // Evitar stock negativo
                $stock = max(0, $stock);
                
                // Ajustar stock máximo si es menor que el actual
                $stockMaximo = max($max, $stock);

                InventarioAlmacen::create([
                    'producto_id'   => $producto->id,
                    'almacen_id'    => $almacen->id,
                    'stock_actual'  => $stock,
                    'stock_minimo'  => $min,
                    'stock_maximo'  => $stockMaximo,
                    'punto_reorden' => (int) round($min * 0.8),
                ]);
            }
        }

        // ═══════════════════════════════════════════════════════════════════
        // 8. CLIENTES + DIRECCIONES
        // ═══════════════════════════════════════════════════════════════════

        $clientesData = [
            [
                'cliente' => [
                    'codigo' => 'CLI-001',
                    'nombre' => 'Supermercado El Colono',
                    'razon_social' => 'El Colono SA de CV',
                    'nit' => '0614-123456-001-0',
                    'tipo' => 'mayorista',
                    'limite_credito' => 15000,
                    'email' => 'compras@elcolono.com',
                    'telefono' => '2333-4444',
                    'celular' => '7744-1234',
                ],
                'direccion' => [
                    'departamento' => 'San Salvador',
                    'municipio' => 'San Salvador',
                    'direccion' => 'Blvd. Los Héroes, local 45',
                    'alias' => 'Oficina Central',
                ],
            ],
            [
                'cliente' => [
                    'codigo' => 'CLI-002',
                    'nombre' => 'Farmacia San Nicolás',
                    'razon_social' => 'Farmacias San Nicolás',
                    'nit' => '0614-234567-002-0',
                    'tipo' => 'minorista',
                    'limite_credito' => 5000,
                    'email' => 'pedidos@fsn.com',
                    'telefono' => '2211-9988',
                    'celular' => '7755-5678',
                ],
                'direccion' => [
                    'departamento' => 'Sonsonate',
                    'municipio' => 'Sonsonate',
                    'direccion' => '4a Calle Poniente #12',
                    'alias' => 'Local Principal',
                ],
            ],
            [
                'cliente' => [
                    'codigo' => 'CLI-003',
                    'nombre' => 'Hotel Sheraton Presidente',
                    'razon_social' => 'Hoteles Presidente SA',
                    'nit' => '0614-345678-003-0',
                    'tipo' => 'corporativo',
                    'limite_credito' => 50000,
                    'email' => 'compras@sheraton.com',
                    'telefono' => '2283-4000',
                    'celular' => '7766-9012',
                ],
                'direccion' => [
                    'departamento' => 'San Salvador',
                    'municipio' => 'San Salvador',
                    'direccion' => 'Final Blvd. Magnolias, Col. San Benito',
                    'alias' => 'Recepción Hotel',
                ],
            ],
            [
                'cliente' => [
                    'codigo' => 'CLI-004',
                    'nombre' => 'Constructora Alas Doradas',
                    'razon_social' => 'Alas Doradas Constructores',
                    'nit' => '0614-456789-004-0',
                    'tipo' => 'mayorista',
                    'limite_credito' => 25000,
                    'email' => 'compras@alasdoradas.com',
                    'telefono' => '2255-1234',
                    'celular' => '7777-3456',
                ],
                'direccion' => [
                    'departamento' => 'La Libertad',
                    'municipio' => 'Santa Tecla',
                    'direccion' => 'Residencial San Isidro, calle 3 #10',
                    'alias' => 'Bodega Principal',
                ],
            ],
            [
                'cliente' => [
                    'codigo' => 'CLI-005',
                    'nombre' => 'Agro Finca San Pablo',
                    'razon_social' => 'Agroindustrias San Pablo',
                    'nit' => '0614-567890-005-0',
                    'tipo' => 'mayorista',
                    'limite_credito' => 18000,
                    'email' => 'ventas@agrofinca.com',
                    'telefono' => '2444-5678',
                    'celular' => '7788-7890',
                ],
                'direccion' => [
                    'departamento' => 'San Miguel',
                    'municipio' => 'San Miguel',
                    'direccion' => 'Km 140 Carretera Panamericana',
                    'alias' => 'Finca Central',
                ],
            ],
            [
                'cliente' => [
                    'codigo' => 'CLI-006',
                    'nombre' => 'Restaurante La Pampa',
                    'razon_social' => 'La Pampa Grill',
                    'nit' => '0614-678901-006-0',
                    'tipo' => 'minorista',
                    'limite_credito' => 8000,
                    'email' => 'pedidos@lapampa.com',
                    'telefono' => '2278-1234',
                    'celular' => '7799-0123',
                ],
                'direccion' => [
                    'departamento' => 'San Salvador',
                    'municipio' => 'Antiguo Cuscatlán',
                    'direccion' => 'Paseo El Carmen, local 8',
                    'alias' => 'Local Antiguo Cuscatlán',
                ],
            ],
            [
                'cliente' => [
                    'codigo' => 'CLI-007',
                    'nombre' => 'Ferretería El Constructor',
                    'razon_social' => 'El Constructor SA',
                    'nit' => '0614-789012-007-0',
                    'tipo' => 'minorista',
                    'limite_credito' => 5000,
                    'email' => 'ventas@elconstructor.com',
                    'telefono' => '2222-7777',
                    'celular' => '7700-4567',
                ],
                'direccion' => [
                    'departamento' => 'Santa Ana',
                    'municipio' => 'Santa Ana',
                    'direccion' => '3a Av. Sur #55',
                    'alias' => 'Tienda Santa Ana',
                ],
            ],
            [
                'cliente' => [
                    'codigo' => 'CLI-008',
                    'nombre' => 'Distribuidora La Lima',
                    'razon_social' => 'La Lima Distribuciones',
                    'nit' => '0614-890123-008-0',
                    'tipo' => 'mayorista',
                    'limite_credito' => 20000,
                    'email' => 'compras@lalima.com',
                    'telefono' => '2333-8888',
                    'celular' => '7711-8901',
                ],
                'direccion' => [
                    'departamento' => 'Usulután',
                    'municipio' => 'Usulután',
                    'direccion' => 'Barrio El Centro, Av. Morazán #3',
                    'alias' => 'Bodega Usulután',
                ],
            ],
        ];

        foreach ($clientesData as $entry) {
            // Verificar si el cliente ya existe por código
            $cliente = Cliente::where('codigo', $entry['cliente']['codigo'])->first();
            
            if (!$cliente) {
                $cliente = Cliente::create(array_merge($entry['cliente'], [
                    'pais' => 'El Salvador',
                    'estado' => 'activo',
                ]));
            }

            // Verificar si ya tiene dirección principal
            $direccionPrincipal = DireccionCliente::where('cliente_id', $cliente->id)
                ->where('es_principal', true)
                ->first();

            if (!$direccionPrincipal) {
                DireccionCliente::create(array_merge($entry['direccion'], [
                    'cliente_id' => $cliente->id,
                    'pais' => 'El Salvador',
                    'es_principal' => true,
                    'activo' => true,
                ]));
            }
        }

        // ═══════════════════════════════════════════════════════════════════
        // 9. TRANSPORTISTAS
        // ═══════════════════════════════════════════════════════════════════

        $transportistasData = [
            [
                'codigo' => 'TRANS-001',
                'nombre' => 'Transportes El Halcón',
                'tipo' => 'externo',
                'vehiculo_tipo' => 'camion',
                'vehiculo_placa' => 'C 123-456',
                'capacidad_kg' => 8000.00,
                'capacidad_m3' => 45.00,
                'tiene_gps' => true,
                'conductor_nombre' => 'Luis Martínez',
                'conductor_licencia' => 'LIC-001',
                'conductor_telefono' => '7700-1111',
                'email' => 'operaciones@elhalcon.com',
                'telefono' => '2244-5678',
                'tarifa_km' => 2.50,
                'tarifa_fija' => 25.00,
                'estado' => 'disponible',
            ],
            [
                'codigo' => 'TRANS-002',
                'nombre' => 'Fletes Express',
                'tipo' => 'externo',
                'vehiculo_tipo' => 'pickup',
                'vehiculo_placa' => 'P 789-012',
                'capacidad_kg' => 1500.00,
                'capacidad_m3' => 8.00,
                'tiene_gps' => false,
                'conductor_nombre' => 'Carlos Pérez',
                'conductor_licencia' => 'LIC-002',
                'conductor_telefono' => '7711-2222',
                'email' => 'info@fletesexpress.com',
                'telefono' => '2255-6789',
                'tarifa_km' => 1.50,
                'tarifa_fija' => 15.00,
                'estado' => 'disponible',
            ],
            [
                'codigo' => 'TRANS-003',
                'nombre' => 'Transporte Propio Central',
                'tipo' => 'propio',
                'vehiculo_tipo' => 'camion',
                'vehiculo_placa' => 'C 456-789',
                'capacidad_kg' => 5000.00,
                'capacidad_m3' => 30.00,
                'tiene_gps' => true,
                'conductor_nombre' => 'Miguel Ángel Rivas',
                'conductor_licencia' => 'LIC-003',
                'conductor_telefono' => '7722-3333',
                'email' => 'transporte@tracelog.com',
                'telefono' => '2233-4455',
                'tarifa_km' => 2.00,
                'tarifa_fija' => 20.00,
                'estado' => 'disponible',
            ],
        ];

        foreach ($transportistasData as $data) {
            Transportista::firstOrCreate(
                ['codigo' => $data['codigo']],
                $data
            );
        }

        // ═══════════════════════════════════════════════════════════════════
        // RESUMEN
        // ═══════════════════════════════════════════════════════════════════

        $this->command->info('');
        $this->command->info('  TraceLog: Base de datos sembrada exitosamente.');
        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════════════════════');
        $this->command->info('  CREDENCIALES DE ACCESO  (contraseña: password)');
        $this->command->info('═══════════════════════════════════════════════════════════');
        $this->command->info('superadmin@tracelog.com     → Rol: super_admin');
        $this->command->info('logistica@tracelog.com      → Rol: logistica');
        $this->command->info('');

        foreach ($sucursalMap as $code => $info) {
            $this->command->info("{$info['nombre']}:");
            $this->command->info("admin.{$code}@tracelog.com   → Rol: admin_sucursal");
            $this->command->info("cajero.{$code}@tracelog.com  → Rol: cajero");
            $this->command->info("bodega.{$code}@tracelog.com  → Rol: supervisor_bodega");
        }

        $this->command->info('');
        $this->command->info('Productos creados      : ' . count($productos));
        $this->command->info('Almacenes creados      : ' . count($almacenes));
        $this->command->info('Clientes creados       : ' . count($clientesData));
        $this->command->info('Transportistas creados : ' . count($transportistasData));
        $this->command->info('Registros inventario   : ' . (count($productos) * count($almacenes)));
        $this->command->info('');
    }
}