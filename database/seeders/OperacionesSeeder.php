<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Notifications\DatabaseNotification;

// Modelos base (ya creados por DatabaseSeeder)
use App\Models\User;
use App\Models\Almacen;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\Proveedor;
use App\Models\Transportista;
use App\Models\InventarioAlmacen;

// Modelos de operaciones
use App\Models\PedidoCompra;
use App\Models\PedidoCompraItem;
use App\Models\PedidoVenta;
use App\Models\PedidoVentaItem;
use App\Models\MovimientoInventario;
use App\Models\Traslado;
use App\Models\TrasladoItem;
use App\Models\SolicitudTraslado;
use App\Models\Envio;
use App\Models\SeguimientoEnvio;

/**
 * OperacionesSeeder
 *
 * Siembra todas las tablas de operaciones que DatabaseSeeder dejó vacías:
 *   pedidos_compra + items
 *   pedidos_venta  + items
 *   movimientos_inventario
 *   traslados      + items
 *   solicitudes_traslado
 *   envios
 *   seguimiento_envios
 *   notifications
 *
 * PRERREQUISITO: ejecutar DatabaseSeeder primero.
 *   php artisan db:seed --class=DatabaseSeeder
 *   php artisan db:seed --class=OperacionesSeeder
 *
 * O agregar al DatabaseSeeder:
 *   $this->call(OperacionesSeeder::class);
 */
class OperacionesSeeder extends Seeder
{
    // ── Helpers de fechas ────────────────────────────────────────────────
    private function diasAtras(int $dias): Carbon
    {
        return Carbon::now()->subDays($dias);
    }

    // ── Generadores de número de documento ──────────────────────────────
    private int $seqCompra  = 1;
    private int $seqVenta   = 1;
    private int $seqMov     = 1;
    private int $seqTraslado = 1;
    private int $seqEnvio   = 1;

    private function numCompra():   string { return 'OC-' . str_pad($this->seqCompra++,   5, '0', STR_PAD_LEFT); }
    private function numVenta():    string { return 'OV-' . str_pad($this->seqVenta++,    5, '0', STR_PAD_LEFT); }
    private function numMov():      string { return 'MOV-' . str_pad($this->seqMov++,     6, '0', STR_PAD_LEFT); }
    private function numTraslado(): string { return 'TRS-' . str_pad($this->seqTraslado++, 5, '0', STR_PAD_LEFT); }
    private function numEnvio():    string { return 'ENV-' . str_pad($this->seqEnvio++,   5, '0', STR_PAD_LEFT); }

    // ────────────────────────────────────────────────────────────────────
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('══════════════════════════════════════════════════════');
        $this->command->info('  OperacionesSeeder — iniciando siembra de operaciones');
        $this->command->info('══════════════════════════════════════════════════════');

        // ─── Cargar entidades base ───────────────────────────────────────
        $almacenes     = Almacen::all();
        $almacenPpal   = Almacen::where('es_principal', true)->first();
        $almacenesOtros= Almacen::where('es_principal', false)->get();
        $productos     = Producto::all();
        $clientes      = Cliente::all();
        $proveedores   = Proveedor::all();
        $transportistas= Transportista::all();

        // Usuarios por rol
        $superAdmin  = User::whereHas('roles', fn($q) => $q->where('name', 'super_admin'))->first();
        $admins      = User::whereHas('roles', fn($q) => $q->where('name', 'admin_sucursal'))->get();
        $cajeros     = User::whereHas('roles', fn($q) => $q->where('name', 'cajero'))->get();
        $bodegueros  = User::whereHas('roles', fn($q) => $q->where('name', 'supervisor_bodega'))->get();
        $userLogistica = User::whereHas('roles', fn($q) => $q->where('name', 'logistica'))->first();

        // Guardia: si no existen entidades base, abortar con mensaje
        if ($almacenes->isEmpty() || $productos->isEmpty()) {
            $this->command->error('No se encontraron almacenes o productos. Ejecuta DatabaseSeeder primero.');
            return;
        }

        // ════════════════════════════════════════════════════════════════
        // 1. PEDIDOS DE COMPRA + ITEMS + MOVIMIENTOS DE ENTRADA
        // ════════════════════════════════════════════════════════════════
        $this->command->line('  [1/7] Pedidos de compra...');

        $escenarios_compra = [
            // [ proveedor_idx, estado, dias_atras, productos_idx[] ]
            [0, 'recibido',   60, [0,1,2,3,4]],  // OC-00001 recibida hace 60 días
            [1, 'recibido',   45, [14,15,16]],    // OC-00002 pinturas recibida
            [2, 'recibido',   30, [8,9,10,11]],   // OC-00003 materiales/fertilizantes
            [0, 'recibido',   20, [5,6,7,17,18]], // OC-00004 herramientas recibida
            [2, 'recibido',   15, [12,13,14]],    // OC-00005 agroquímicos recibida
            [0, 'confirmado', 10, [20,21,22,23]], // OC-00006 confirmada, aún no recibida
            [1, 'enviado',     7, [15,16]],       // OC-00007 enviada al proveedor
            [2, 'borrador',    3, [9,10]],        // OC-00008 borrador
            [0, 'parcial',     5, [0,1,2]],       // OC-00009 recibida parcialmente
            [0, 'cancelado',  25, [3,4]],         // OC-00010 cancelada
        ];

        $pedidosCompra = [];
        foreach ($escenarios_compra as [$provIdx, $estado, $dias, $prodIdxs]) {
            $proveedor = $proveedores[$provIdx % $proveedores->count()];
            $admin     = $admins->random();
            $fechaPed  = $this->diasAtras($dias);
            $fechaRec  = in_array($estado, ['recibido','parcial'])
                ? $fechaPed->copy()->addDays(rand(2, 5))
                : null;

            $subtotal = 0;
            $itemsData = [];
            foreach ($prodIdxs as $pIdx) {
                $prod    = $productos[$pIdx % $productos->count()];
                $cant    = rand(10, 50);
                $precio  = $prod->precio_compra;
                $itemsData[] = ['producto' => $prod, 'cantidad' => $cant, 'precio' => $precio];
                $subtotal += $cant * $precio;
            }
            $impuesto  = round($subtotal * 0.13, 2); // IVA 13 % El Salvador
            $total     = $subtotal + $impuesto;

            $pedido = PedidoCompra::create([
                'numero'              => $this->numCompra(),
                'proveedor_id'        => $proveedor->id,
                'user_id'             => $admin->id,
                'fecha_pedido'        => $fechaPed->toDateString(),
                'fecha_requerida'     => $fechaPed->copy()->addDays(7)->toDateString(),
                'fecha_recepcion'     => $fechaRec?->toDateString(),
                'estado'              => $estado,
                'subtotal'            => $subtotal,
                'impuesto'            => $impuesto,
                'descuento'           => 0,
                'total'               => $total,
                'moneda'              => 'USD',
                'condiciones_pago'    => $estado === 'cancelado' ? null : 'Crédito 30 días',
                'notas'               => $estado === 'borrador' ? 'Pendiente revisión de precios' : null,
                'motivo_cancelacion'  => $estado === 'cancelado' ? 'Precios fuera de rango acordado' : null,
                'created_at'          => $fechaPed,
                'updated_at'          => $fechaPed,
            ]);
            $pedidosCompra[] = $pedido;

            foreach ($itemsData as $itemData) {
                $cantRecibida = match($estado) {
                    'recibido'  => $itemData['cantidad'],
                    'parcial'   => (int) round($itemData['cantidad'] * 0.6),
                    default     => 0,
                };
                PedidoCompraItem::create([
                    'pedido_compra_id'  => $pedido->id,
                    'producto_id'       => $itemData['producto']->id,
                    'cantidad'   => $itemData['cantidad'],
                    'cantidad_recibida' => $cantRecibida,
                    'precio_unitario'   => $itemData['precio'],
                    'subtotal'          => round($itemData['cantidad'] * $itemData['precio'], 2),
                    'unidad_medida'     => 'unidad',
                    'created_at'        => $fechaPed,
                    'updated_at'        => $fechaPed,
                ]);

                // Movimiento de inventario por recepción
                if ($cantRecibida > 0) {
                    $inv = InventarioAlmacen::where('producto_id', $itemData['producto']->id)
                        ->where('almacen_id', $almacenPpal->id)
                        ->first();
                    $stockAnt = $inv ? $inv->stock_actual : 0;
                    MovimientoInventario::create([
                        'numero'            => $this->numMov(),
                        'producto_id'       => $itemData['producto']->id,
                        'almacen_id'        => $almacenPpal->id,
                        'user_id'           => $admin->id,
                        'tipo'              => 'entrada_compra',
                        'cantidad'          => $cantRecibida,
                        'stock_anterior'    => $stockAnt,
                        'stock_nuevo'       => $stockAnt + $cantRecibida,
                        'costo_unitario'    => $itemData['precio'],
                        'costo_total'       => round($cantRecibida * $itemData['precio'], 2),
                        'referencia_type'   => 'App\\Models\\PedidoCompra',
                        'referencia_id'     => $pedido->id,
                        'lote'              => 'LOTE-' . strtoupper(substr(md5($pedido->id . $itemData['producto']->id), 0, 6)),
                        'fecha_vencimiento' => null,
                        'motivo'            => "Recepción OC {$pedido->numero}",
                        'fecha_movimiento'  => $fechaRec ?? $fechaPed,
                        'created_at'        => $fechaRec ?? $fechaPed,
                        'updated_at'        => $fechaRec ?? $fechaPed,
                    ]);
                }
            }
        }
        $this->command->line('     → ' . count($pedidosCompra) . ' pedidos de compra creados.');

        // ════════════════════════════════════════════════════════════════
        // 2. PEDIDOS DE VENTA + ITEMS + MOVIMIENTOS DE SALIDA
        // ════════════════════════════════════════════════════════════════
        $this->command->line('  [2/7] Pedidos de venta...');

        // Mapa cliente → almacén más cercano (por depto) para mayor realismo
        $clienteAlmacenMap = [
            'CLI-001' => $almacenPpal,                                // San Salvador
            'CLI-002' => $almacenesOtros->firstWhere('nombre', 'LIKE', '%Sonsonate%') ?? $almacenPpal,
            'CLI-003' => $almacenPpal,
            'CLI-004' => $almacenesOtros->firstWhere('nombre', 'LIKE', '%La Libertad%') ?? $almacenPpal,
            'CLI-005' => $almacenesOtros->firstWhere('nombre', 'LIKE', '%San Miguel%') ?? $almacenPpal,
            'CLI-006' => $almacenPpal,
            'CLI-007' => $almacenesOtros->firstWhere('nombre', 'LIKE', '%Santa Ana%') ?? $almacenPpal,
            'CLI-008' => $almacenesOtros->firstWhere('nombre', 'LIKE', '%Usulután%') ?? $almacenPpal,
        ];

        $escenarios_venta = [
            // [cliente_codigo, estado, dias_atras, productos_idx[], almacen_key]
            ['CLI-001', 'entregado',  55, [0,1,4,5],    'CLI-001'],
            ['CLI-003', 'entregado',  50, [14,15,16],   'CLI-003'],
            ['CLI-004', 'entregado',  40, [8,9,10],     'CLI-004'],
            ['CLI-005', 'entregado',  35, [11,12,13],   'CLI-005'],
            ['CLI-001', 'entregado',  25, [20,21,22,23],'CLI-001'],
            ['CLI-002', 'entregado',  20, [5,6,7],      'CLI-002'],
            ['CLI-007', 'entregado',  18, [17,18,19],   'CLI-007'],
            ['CLI-008', 'entregado',  15, [14,15],      'CLI-008'],
            ['CLI-006', 'entregado',  10, [26,27,28],   'CLI-006'],
            ['CLI-003', 'confirmado', 8,  [0,2,3],      'CLI-003'],
            ['CLI-004', 'confirmado', 6,  [9,10,11],    'CLI-004'],
            ['CLI-001', 'borrador',   3,  [4,5,6],      'CLI-001'],
            ['CLI-002', 'cancelado',  30, [20,21],      'CLI-002'],
            ['CLI-005', 'entregado',  12, [24,25,26],   'CLI-005'],
            ['CLI-007', 'confirmado', 4,  [1,2],        'CLI-007'],
        ];

        $pedidosVenta = [];
        foreach ($escenarios_venta as [$codigoCliente, $estado, $dias, $prodIdxs, $mapKey]) {
            $cliente = Cliente::where('codigo', $codigoCliente)->first() ?? $clientes->random();
            $almacen = $clienteAlmacenMap[$mapKey] ?? $almacenPpal;
            $cajero  = $cajeros->random();
            $fecha   = $this->diasAtras($dias);

            $subtotal  = 0;
            $itemsData = [];
            foreach ($prodIdxs as $pIdx) {
                $prod   = $productos[$pIdx % $productos->count()];
                $cant   = rand(2, 15);
                $precio = $prod->precio_venta;
                $desc   = in_array($cliente->tipo, ['mayorista','corporativo']) ? 5.00 : 0.00;
                $sub    = round($cant * $precio * (1 - $desc / 100), 2);
                $itemsData[] = ['producto' => $prod, 'cantidad' => $cant, 'precio' => $precio, 'descuento' => $desc, 'subtotal' => $sub];
                $subtotal += $sub;
            }
            $impuesto = round($subtotal * 0.13, 2);
            $total    = $subtotal + $impuesto;

            $pedidoV = PedidoVenta::create([
                'numero'           => $this->numVenta(),
                'cliente_id'       => $cliente->id,
                'user_id'          => $cajero->id,
                'almacen_id'       => $almacen->id,
                'fecha_pedido'     => $fecha->toDateString(),
                'fecha_requerida'  => $fecha->copy()->addDays(3)->toDateString(),
                'fecha_entrega_real' => in_array($estado, ['entregado']) ? $fecha->copy()->addDays(rand(2,5))->toDateString() : null,
                'estado'           => $estado,
                'subtotal'         => $subtotal,
                'impuesto'         => $impuesto,
                'descuento'        => $estado === 'cancelado' ? 0 : ($subtotal * 0.0),
                'total'            => $total,
                'moneda'           => 'USD',
                'direccion_entrega' => $cliente->direccion_principal ?? 'Dirección no registrada',
                'departamento_entrega' => $cliente->departamento ?? 'San Salvador',
                'municipio_entrega' => $cliente->municipio ?? 'San Salvador',
                'notas'            => match($estado) {
                    'borrador'   => 'Pendiente confirmación de precios con cliente',
                    'cancelado'  => null,
                    default      => null,
                },
                'created_at'       => $fecha,
                'updated_at'       => $fecha,
            ]);
            $pedidosVenta[] = $pedidoV;

            foreach ($itemsData as $itemData) {
                $cantDesp = in_array($estado, ['aprobado']) ? $itemData['cantidad'] : 0;
                PedidoVentaItem::create([
                    'pedido_venta_id'     => $pedidoV->id,
                    'producto_id'         => $itemData['producto']->id,
                    'cantidad'            => $itemData['cantidad'],
                    'cantidad_despachada' => $cantDesp,
                    'precio_unitario'     => $itemData['precio'],
                    'descuento'           => $itemData['descuento'],
                    'subtotal'            => $itemData['subtotal'],
                    'unidad_medida'       => 'unidad',
                    'notas'               => null,
                    'created_at'          => $fecha,
                    'updated_at'          => $fecha,
                ]);

                // Movimiento de salida por venta aprobada
                if ($cantDesp > 0) {
                    $inv = InventarioAlmacen::where('producto_id', $itemData['producto']->id)
                        ->where('almacen_id', $almacen->id)
                        ->first();
                    $stockAnt = $inv ? max(0, $inv->stock_actual) : 0;
                    $stockNvo = max(0, $stockAnt - $cantDesp);
                    MovimientoInventario::create([
                        'numero'           => $this->numMov(),
                        'producto_id'      => $itemData['producto']->id,
                        'almacen_id'       => $almacen->id,
                        'user_id'          => $cajero->id,
                        'tipo'             => 'salida_venta',
                        'cantidad'         => $cantDesp,
                        'stock_anterior'   => $stockAnt,
                        'stock_nuevo'      => $stockNvo,
                        'costo_unitario'   => $itemData['producto']->precio_compra,
                        'costo_total'      => round($cantDesp * $itemData['producto']->precio_compra, 2),
                        'referencia_type'  => 'App\\Models\\PedidoVenta',
                        'referencia_id'    => $pedidoV->id,
                        'lote'             => null,
                        'fecha_vencimiento'=> null,
                        'motivo'           => "Despacho OV {$pedidoV->numero}",
                        'fecha_movimiento' => $fecha->copy()->addDays(1),
                        'created_at'       => $fecha,
                        'updated_at'       => $fecha,
                    ]);
                }
            }
        }
        $this->command->line('     → ' . count($pedidosVenta) . ' pedidos de venta creados.');

        // ════════════════════════════════════════════════════════════════
        // 3. AJUSTES DE INVENTARIO (ajuste_positivo / ajuste_negativo / merma)
        // ════════════════════════════════════════════════════════════════
        $this->command->line('  [3/7] Ajustes de inventario...');

        $ajustes = [
            // [producto_idx, almacen, tipo, cantidad, dias, motivo]
            [2,  $almacenPpal,                                             'ajuste_positivo',  5, 28, 'Inventario físico — diferencia positiva encontrada'],
            [7,  $almacenesOtros->first() ?? $almacenPpal,                 'ajuste_negativo',  3, 22, 'Inventario físico — faltante detectado en conteo'],
            [11, $almacenPpal,                                             'ajuste_positivo',  8, 18, 'Corrección de ingreso anterior incompleto'],
            [14, $almacenesOtros->skip(1)->first() ?? $almacenPpal,        'merma',            4, 14, 'Producto dañado durante almacenamiento'],
            [17, $almacenPpal,                                             'ajuste_negativo',  2, 10, 'Muestra otorgada a cliente potencial'],
            [19, $almacenesOtros->first() ?? $almacenPpal,                 'merma',            1,  7, 'Fuga detectada en envase de lubricante'],
            [8,  $almacenPpal,                                             'inventario_inicial',50,90, 'Carga inicial de inventario al sistema'],
            [24, $almacenesOtros->skip(2)->first() ?? $almacenPpal,        'ajuste_positivo',  10, 5, 'Conteo físico — sobrante detectado'],
        ];

        foreach ($ajustes as [$prodIdx, $almacen, $tipo, $cantidad, $dias, $motivo]) {
            $prod    = $productos[$prodIdx % $productos->count()];
            $bodega  = $bodegueros->random();
            $fecha   = $this->diasAtras($dias);
            $inv     = InventarioAlmacen::where('producto_id', $prod->id)
                ->where('almacen_id', $almacen->id)->first();
            $stockAnt = $inv ? max(0, $inv->stock_actual) : 0;
            $stockNvo = in_array($tipo, ['ajuste_positivo', 'inventario_inicial'])
                ? $stockAnt + $cantidad
                : max(0, $stockAnt - $cantidad);

            MovimientoInventario::create([
                'numero'           => $this->numMov(),
                'producto_id'      => $prod->id,
                'almacen_id'       => $almacen->id,
                'user_id'          => $bodega->id,
                'tipo'             => $tipo,
                'cantidad'         => $cantidad,
                'stock_anterior'   => $stockAnt,
                'stock_nuevo'      => $stockNvo,
                'costo_unitario'   => $prod->precio_compra,
                'costo_total'      => round($cantidad * $prod->precio_compra, 2),
                'referencia_type'  => null,
                'referencia_id'    => null,
                'lote'             => null,
                'fecha_vencimiento'=> null,
                'motivo'           => $motivo,
                'fecha_movimiento' => $fecha,
                'created_at'       => $fecha,
                'updated_at'       => $fecha,
            ]);
        }
        $this->command->line('     → ' . count($ajustes) . ' ajustes de inventario creados.');

        // ════════════════════════════════════════════════════════════════
        // 4. TRASLADOS ENTRE ALMACENES + ITEMS + MOVIMIENTOS
        // ════════════════════════════════════════════════════════════════
        $this->command->line('  [4/7] Traslados...');

        $almacenSS = $almacenPpal; // San Salvador = origen habitual
        $almacenSA = $almacenesOtros->firstWhere('nombre', 'LIKE', '%Santa Ana%')   ?? $almacenesOtros->first();
        $almacenSM = $almacenesOtros->firstWhere('nombre', 'LIKE', '%San Miguel%')  ?? $almacenesOtros->skip(1)->first();
        $almacenLL = $almacenesOtros->firstWhere('nombre', 'LIKE', '%La Libertad%') ?? $almacenesOtros->skip(2)->first();
        $almacenSO = $almacenesOtros->firstWhere('nombre', 'LIKE', '%Sonsonate%')   ?? $almacenesOtros->skip(3)->first();
        $almacenUS = $almacenesOtros->firstWhere('nombre', 'LIKE', '%Usulután%')    ?? $almacenesOtros->last();

        $escenarios_traslado = [
            // [origen, destino, estado, dias, productos_idx[], motivo]
            [$almacenSS, $almacenSA, 'completado', 50, [0,1,4,5],    'Reabastecimiento mensual — sucursal Santa Ana'],
            [$almacenSS, $almacenSM, 'completado', 45, [8,9,11,12],  'Reabastecimiento mensual — sucursal San Miguel'],
            [$almacenSS, $almacenLL, 'completado', 35, [14,15,16],   'Envío de pinturas para temporada alta'],
            [$almacenSS, $almacenSO, 'completado', 28, [17,18,19],   'Ferretería general — reposición Sonsonate'],
            [$almacenSS, $almacenUS, 'completado', 20, [5,6,7],      'Herramientas manuales — pedido sucursal Usulután'],
            [$almacenSS, $almacenSA, 'aprobado',   12, [20,21,22],   'Traslado de herramientas eléctricas aprobado'],
            [$almacenSA, $almacenSS, 'aprobado',    8, [15,16],      'Devolución de excedente pinturas a central'],
            [$almacenSS, $almacenSM, 'sugerido',    5, [24,25,26],   'Sistema: bajo stock en San Miguel'],
            [$almacenSS, $almacenLL, 'cancelado',  30, [9,10],       'Cancelado — error en cantidades solicitadas'],
        ];

        foreach ($escenarios_traslado as [$origen, $destino, $estado, $dias, $prodIdxs, $motivo]) {
            if (!$origen || !$destino) continue;

            $fecha     = $this->diasAtras($dias);
            $creadoPor = $bodegueros->random();
            $aprobPor  = in_array($estado, ['aprobado','completado']) ? ($superAdmin ?? $admins->random()) : null;

            $traslado = Traslado::create([
                'numero'              => $this->numTraslado(),
                'almacen_origen_id'   => $origen->id,
                'almacen_destino_id'  => $destino->id,
                'estado'              => $estado,
                'motivo'              => $motivo,
                'creado_por'          => $creadoPor->id,
                'aprobado_por'        => $aprobPor?->id,
                'fecha_aprobacion'    => $aprobPor ? $fecha->copy()->addDay() : null,
                'fecha_completado'    => $estado === 'completado' ? $fecha->copy()->addDays(2) : null,
                'created_at'          => $fecha,
                'updated_at'          => $fecha,
            ]);

            foreach ($prodIdxs as $pIdx) {
                $prod        = $productos[$pIdx % $productos->count()];
                $cantSuger   = rand(5, 20);
                $cantReal    = in_array($estado, ['completado']) ? $cantSuger : null;
                $lote        = 'LOTE-' . strtoupper(substr(md5($traslado->id . $prod->id), 0, 6));

                TrasladoItem::create([
                    'traslado_id'        => $traslado->id,
                    'producto_id'        => $prod->id,
                    'cantidad_sugerida'  => $cantSuger,
                    'cantidad_real'      => $cantReal,
                    'lote'               => $lote,
                    'fecha_vencimiento'  => null,
                    'notas'              => null,
                    'created_at'         => $fecha,
                    'updated_at'         => $fecha,
                ]);

                // Movimientos de salida (origen) y entrada (destino) sólo si completado
                if ($estado === 'completado') {
                    foreach ([
                                 ['almacen' => $origen,  'tipo' => 'traslado_salida', 'sign' => -1],
                                 ['almacen' => $destino, 'tipo' => 'traslado_entrada','sign' =>  1],
                             ] as $mov) {
                        $inv     = InventarioAlmacen::where('producto_id', $prod->id)
                            ->where('almacen_id', $mov['almacen']->id)->first();
                        $stockAnt = $inv ? max(0, $inv->stock_actual) : 0;
                        $stockNvo = max(0, $stockAnt + $mov['sign'] * $cantSuger);

                        MovimientoInventario::create([
                            'numero'           => $this->numMov(),
                            'producto_id'      => $prod->id,
                            'almacen_id'       => $mov['almacen']->id,
                            'user_id'          => $creadoPor->id,
                            'tipo'             => $mov['tipo'],
                            'cantidad'         => $cantSuger,
                            'stock_anterior'   => $stockAnt,
                            'stock_nuevo'      => $stockNvo,
                            'costo_unitario'   => $prod->precio_compra,
                            'costo_total'      => round($cantSuger * $prod->precio_compra, 2),
                            'referencia_type'  => 'App\\Models\\Traslado',
                            'referencia_id'    => $traslado->id,
                            'lote'             => $lote,
                            'fecha_vencimiento'=> null,
                            'motivo'           => "{$mov['tipo']} TRS {$traslado->numero}",
                            'fecha_movimiento' => $traslado->fecha_completado,
                            'created_at'       => $fecha,
                            'updated_at'       => $fecha,
                        ]);
                    }
                }
            }
        }
        $this->command->line('     → ' . count($escenarios_traslado) . ' traslados creados.');

        // ════════════════════════════════════════════════════════════════
        // 5. SOLICITUDES DE TRASLADO (automáticas por stock bajo)
        // ════════════════════════════════════════════════════════════════
        $this->command->line('  [5/7] Solicitudes de traslado...');

        $solicitudesData = [
            // [almacen_solicitante, almacen_origen, producto_idx, cantidad, estado, dias]
            [$almacenSM, $almacenSS, 3,  15, 'aprobada',   20],
            [$almacenSA, $almacenSS, 9,  40, 'aprobada',   17],
            [$almacenUS, $almacenSS, 14, 20, 'pendiente',  10],
            [$almacenLL, $almacenSS, 0,  10, 'pendiente',   8],
            [$almacenSO, $almacenSS, 11, 25, 'rechazada',  25],
            [$almacenSM, $almacenSS, 17, 30, 'pendiente',   4],
            [$almacenSA, $almacenSS, 20,  8, 'aprobada',   14],
            [$almacenUS, $almacenSS, 5,  20, 'pendiente',   2],
        ];

        foreach ($solicitudesData as [$almDest, $almOrig, $prodIdx, $cant, $estado, $dias]) {
            if (!$almDest || !$almOrig) continue;
            $prod   = $productos[$prodIdx % $productos->count()];
            $bodega = $bodegueros->random();
            $fecha  = $this->diasAtras($dias);

            SolicitudTraslado::create([
                'almacen_solicitante_id' => $almDest->id,
                'almacen_origen_id'      => $almOrig->id,
                'producto_id'            => $prod->id,
                'cantidad_solicitada'    => $cant,
                'estado'                 => $estado,
                'motivo'                 => 'Stock por debajo del punto de reorden',
                'notas'                  => $estado === 'rechazada' ? 'Almacén origen sin stock suficiente en ese momento' : null,
                'solicitado_por'         => $bodega->id,
                'aprobado_por'           => in_array($estado, ['aprobada']) ? ($superAdmin ?? $admins->random())->id : null,
                'fecha_solicitud'        => $fecha,
                'fecha_aprobacion'       => $estado === 'aprobada' ? $fecha->copy()->addDay() : null,
                'created_at'             => $fecha,
                'updated_at'             => $fecha,
            ]);
        }
        $this->command->line('     → ' . count($solicitudesData) . ' solicitudes de traslado creadas.');

        // ════════════════════════════════════════════════════════════════
        // 6. ENVÍOS + SEGUIMIENTO
        // ════════════════════════════════════════════════════════════════
        $this->command->line('  [6/7] Envíos y seguimiento...');

        // Tomar los pedidos de venta aprobados para generar envíos
        $pedidosAprobados = collect($pedidosVenta)
            ->filter(fn($p) => $p->estado === 'aprobado')
            ->take(10);

        $estadosEnvio = [
            'entregado', 'entregado', 'entregado', 'entregado', 'entregado',
            'en_ruta', 'en_ruta',
            'preparando', 'preparando',
            'cancelado',
        ];

        foreach ($pedidosAprobados as $idx => $pedidoV) {
            $transportista = $transportistas->random();
            $estado        = $estadosEnvio[$idx] ?? 'entregado';
            $fechaEnvio    = Carbon::parse($pedidoV->created_at)->addDays(1);
            $userLog       = $userLogistica ?? $admins->random();

            // Coordenadas aproximadas de los departamentos de El Salvador
            $coordsDestino = [
                'San Salvador'       => ['lat' => 13.6929, 'lng' => -89.2182],
                'Santa Ana'          => ['lat' => 13.9946, 'lng' => -89.5597],
                'San Miguel'         => ['lat' => 13.4833, 'lng' => -88.1833],
                'La Libertad'        => ['lat' => 13.4861, 'lng' => -89.3219],
                'Sonsonate'          => ['lat' => 13.7195, 'lng' => -89.7232],
                'Usulután'           => ['lat' => 13.3500, 'lng' => -88.4500],
                'Antiguo Cuscatlán'  => ['lat' => 13.6703, 'lng' => -89.2527],
                'Santa Tecla'        => ['lat' => 13.6750, 'lng' => -89.2793],
            ];

            $depto = $pedidoV->cliente->direcciones->first()?->departamento ?? 'San Salvador';
            $coords = $coordsDestino[$depto] ?? $coordsDestino['San Salvador'];

            $peso  = round(rand(10, 200) + rand(0, 99) / 100, 2);
            $vol   = round($peso / 200, 3);

            $envio = Envio::create([
                'numero'              => $this->numEnvio(),
                'pedido_venta_id'     => $pedidoV->id,
                'transportista_id'    => $transportista->id,
                'user_id'             => $userLog->id,
                'estado'              => $estado,
                'direccion_entrega'   => $pedidoV->cliente->direcciones->first()?->direccion ?? 'Dirección pendiente',
                'departamento'        => $depto,
                'municipio'           => $pedidoV->cliente->direcciones->first()?->municipio ?? 'San Salvador',
                'referencia'          => 'Frente a supermercado, portón azul',
                'peso_kg'             => $peso,
                'volumen_m3'          => $vol,
                'costo_envio'         => round(($transportista->tarifa_fija ?? 20) + ($transportista->tarifa_km ?? 2) * rand(5, 60), 2),
                'fecha_despacho'      => $fechaEnvio->toDateString(),
                'fecha_entrega_estimada' => $fechaEnvio->copy()->addDays(1)->toDateString(),
                'fecha_entrega_real'  => in_array($estado, ['entregado']) ? $fechaEnvio->copy()->addDays(rand(1, 3))->toDateString() : null,
                'firma_receptor'      => $estado === 'entregado' ? 'Recibido conforme — ' . $pedidoV->cliente->nombre : null,
                'notas'               => null,
                'created_at'          => $fechaEnvio,
                'updated_at'          => $fechaEnvio,
            ]);

            // ── Eventos de seguimiento según estado ───────────────────
            $eventosBase = [
                ['evento' => 'Pedido preparado en bodega',    'descripcion' => 'El pedido fue revisado, empacado y listo para despacho.', 'offset_horas' => 0],
                ['evento' => 'Recogido por transportista',    'descripcion' => 'El transportista retiró el paquete del almacén.', 'offset_horas' => 2],
                ['evento' => 'En tránsito',                   'descripcion' => 'El envío está en camino al destino.', 'offset_horas' => 4],
            ];

            if (in_array($estado, ['en_ruta', 'entregado'])) {
                $eventosBase[] = ['evento' => 'Llegó a zona de entrega', 'descripcion' => 'El vehículo está en el departamento de destino.', 'offset_horas' => 6];
            }
            if ($estado === 'entregado') {
                $eventosBase[] = ['evento' => 'Entregado al cliente', 'descripcion' => 'El pedido fue entregado satisfactoriamente al cliente.', 'offset_horas' => 8];
            }
            if ($estado === 'cancelado') {
                $eventosBase = [
                    ['evento' => 'Pedido preparado en bodega', 'descripcion' => 'El pedido fue empacado.', 'offset_horas' => 0],
                    ['evento' => 'Envío cancelado',            'descripcion' => 'El envío fue cancelado a solicitud del cliente.', 'offset_horas' => 3],
                ];
            }

            foreach ($eventosBase as $ev) {
                $fechaEvento = $fechaEnvio->copy()->addHours($ev['offset_horas']);
                SeguimientoEnvio::create([
                    'envio_id'    => $envio->id,
                    'evento'      => $ev['evento'],
                    'descripcion' => $ev['descripcion'],
                    'ubicacion'   => match($ev['evento']) {
                        'Pedido preparado en bodega', 'Recogido por transportista' => 'San Salvador, Alamacén Central',
                        'En tránsito'                                              => 'Carretera Panamericana',
                        'Llegó a zona de entrega'                                  => $depto . ', El Salvador',
                        'Entregado al cliente'                                     => $pedidoV->cliente->direcciones->first()?->municipio ?? $depto,
                        default                                                    => 'San Salvador',
                    },
                    'latitud'     => round($coords['lat'] + rand(-100, 100) / 10000, 8),
                    'longitud'    => round($coords['lng'] + rand(-100, 100) / 10000, 8),
                    'registrado_por' => $userLog->id,
                    'created_at'  => $fechaEvento,
                    'updated_at'  => $fechaEvento,
                ]);
            }
        }
        $this->command->line('     → ' . $pedidosAprobados->count() . ' envíos con seguimiento creados.');

        // ════════════════════════════════════════════════════════════════
        // 7. NOTIFICACIONES (tabla notifications de Laravel)
        // ════════════════════════════════════════════════════════════════
        $this->command->line('  [7/7] Notificaciones...');

        $notificaciones = [
            [
                'user'  => $superAdmin,
                'type'  => 'App\\Notifications\\StockBajoAlerta',
                'data'  => [
                    'titulo'   => 'Stock bajo detectado',
                    'mensaje'  => 'El producto "Cemento Gris 42.5 kg" en San Miguel está por debajo del punto de reorden.',
                    'url'      => '/inventario',
                    'icono'    => 'warning',
                ],
                'leida' => false,
                'dias'  => 2,
            ],
            [
                'user'  => $admins->first(),
                'type'  => 'App\\Notifications\\NuevoPedidoVenta',
                'data'  => [
                    'titulo'   => 'Nuevo pedido de venta',
                    'mensaje'  => 'Se recibió el pedido OV-00003 de Hotel Sheraton Presidente por $' . number_format(rand(800, 3000), 2),
                    'url'      => '/ventas',
                    'icono'    => 'shopping-cart',
                ],
                'leida' => false,
                'dias'  => 1,
            ],
            [
                'user'  => $userLogistica ?? $admins->first(),
                'type'  => 'App\\Notifications\\EnvioEntregado',
                'data'  => [
                    'titulo'   => 'Envío entregado',
                    'mensaje'  => 'El envío ENV-00001 fue entregado satisfactoriamente al cliente.',
                    'url'      => '/envios',
                    'icono'    => 'check-circle',
                ],
                'leida' => true,
                'dias'  => 5,
            ],
            [
                'user'  => $superAdmin,
                'type'  => 'App\\Notifications\\PedidoCompraRecibido',
                'data'  => [
                    'titulo'   => 'Orden de compra recibida',
                    'mensaje'  => 'La orden OC-00001 de Truper El Salvador fue recibida completamente.',
                    'url'      => '/compras',
                    'icono'    => 'package',
                ],
                'leida' => true,
                'dias'  => 58,
            ],
            [
                'user'  => $bodegueros->first(),
                'type'  => 'App\\Notifications\\TrasladoAprobado',
                'data'  => [
                    'titulo'   => 'Traslado aprobado',
                    'mensaje'  => 'El traslado TRS-00006 de San Salvador a Santa Ana fue aprobado. Procede con el despacho.',
                    'url'      => '/traslados',
                    'icono'    => 'truck',
                ],
                'leida' => false,
                'dias'  => 11,
            ],
            [
                'user'  => $admins->skip(1)->first() ?? $admins->first(),
                'type'  => 'App\\Notifications\\SolicitudTrasladoPendiente',
                'data'  => [
                    'titulo'   => 'Solicitud de traslado pendiente',
                    'mensaje'  => 'La sucursal La Libertad requiere 10 unidades de "Taladro Percutor 1/2\"" con urgencia.',
                    'url'      => '/traslados/solicitudes',
                    'icono'    => 'alert-circle',
                ],
                'leida' => false,
                'dias'  => 7,
            ],
            [
                'user'  => $superAdmin,
                'type'  => 'App\\Notifications\\ReporteListoExportar',
                'data'  => [
                    'titulo'   => 'Reporte mensual listo',
                    'mensaje'  => 'El reporte de ventas del mes anterior ya está disponible para exportar.',
                    'url'      => '/reportes',
                    'icono'    => 'file-text',
                ],
                'leida' => true,
                'dias'  => 32,
            ],
            [
                'user'  => $cajeros->first(),
                'type'  => 'App\\Notifications\\ClienteLimiteCreditoAlerta',
                'data'  => [
                    'titulo'   => 'Cliente cerca del límite de crédito',
                    'mensaje'  => 'Constructora Alas Doradas ha usado el 87% de su límite de crédito ($25,000).',
                    'url'      => '/clientes',
                    'icono'    => 'credit-card',
                ],
                'leida' => false,
                'dias'  => 3,
            ],
        ];

        foreach ($notificaciones as $notif) {
            if (!$notif['user']) continue;
            $fecha = $this->diasAtras($notif['dias']);
            DB::table('notifications')->insert([
                'id'              => \Illuminate\Support\Str::uuid()->toString(),
                'type'            => $notif['type'],
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id'   => $notif['user']->id,
                'data'            => json_encode($notif['data']),
                'read_at'         => $notif['leida'] ? $fecha->copy()->addHours(rand(1, 12)) : null,
                'created_at'      => $fecha,
                'updated_at'      => $fecha,
            ]);
        }
        $this->command->line('     → ' . count($notificaciones) . ' notificaciones creadas.');

        // ════════════════════════════════════════════════════════════════
        // RESUMEN FINAL
        // ════════════════════════════════════════════════════════════════
        $this->command->info('');
        $this->command->info('══════════════════════════════════════════════════════');
        $this->command->info('  OperacionesSeeder — completado exitosamente ✔');
        $this->command->info('══════════════════════════════════════════════════════');
        $this->command->table(
            ['Tabla', 'Registros'],
            [
                ['pedidos_compra',        PedidoCompra::count()],
                ['pedidos_compra_items',  PedidoCompraItem::count()],
                ['pedidos_venta',         PedidoVenta::count()],
                ['pedidos_venta_items',   PedidoVentaItem::count()],
                ['movimientos_inventario',MovimientoInventario::count()],
                ['traslados',             Traslado::count()],
                ['traslados_items',       TrasladoItem::count()],
                ['solicitudes_traslado',  SolicitudTraslado::count()],
                ['envios',                Envio::count()],
                ['seguimiento_envios',    SeguimientoEnvio::count()],
                ['notifications',         DB::table('notifications')->count()],
            ]
        );
        $this->command->info('');
    }
}