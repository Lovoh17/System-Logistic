<?php

namespace App\Filament\Pages;

use App\Models\Cliente;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\PedidoVenta;
use App\Models\PedidoVentaItem;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class PuntoVenta extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Punto de Venta';
    protected static ?string $title = 'Punto de Venta — Caja';
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament.pages.punto-venta';

    public ?int $cliente_id = null;
    public array $items = [];
    public ?int $producto_seleccionado_id = null;
    public int $cantidad = 1;
    public string $searchTerm = '';

    public function mount(): void
    {
        $this->items = session()->get('carrito_ventas', []);
    }

    // Obtener clientes
    public function getClientesProperty()
    {
        return Cliente::where('estado', 'activo')->orderBy('nombre')->get();
    }

    // Obtener productos con búsqueda en vivo
    public function getProductosProperty()
    {
        $query = Producto::where('estado', 'activo')
            ->where('stock_actual', '>', 0);
        
        if (!empty($this->searchTerm) && strlen($this->searchTerm) >= 1) {
            $query->where(function ($q) {
                $q->where('nombre', 'like', "%{$this->searchTerm}%")
                  ->orWhere('codigo', 'like', "%{$this->searchTerm}%")
                  ->orWhere('sku', 'like', "%{$this->searchTerm}%");
            });
        }
        
        return $query->orderBy('nombre')->limit(50)->get();
    }

    // Obtener productos destacados
    public function getProductosRapidosProperty()
    {
        return Producto::where('estado', 'activo')
            ->where('stock_actual', '>', 0)
            ->orderByDesc('stock_actual')
            ->limit(12)
            ->get();
    }

    public function getTotalProperty()
    {
        return collect($this->items)->sum('subtotal');
    }

    public function getIvaProperty()
    {
        return $this->total * 0.13;
    }

    public function getTotalConIvaProperty()
    {
        return $this->total * 1.13;
    }

    public function getItemsCountProperty()
    {
        return (int) collect($this->items)->sum('cantidad');
    }

    public function agregarProductoDesdeSelect()
    {
        if (!$this->producto_seleccionado_id) {
            Notification::make()->title('Seleccione un producto')->warning()->send();
            return;
        }

        $this->agregarProducto($this->producto_seleccionado_id, $this->cantidad);
        $this->producto_seleccionado_id = null;
        $this->cantidad = 1;
    }

    public function agregarProducto(int $productoId, int $cantidad = 1): void
    {
        $producto = Producto::find($productoId);

        if (!$producto) {
            Notification::make()->title('Producto no encontrado')->danger()->send();
            return;
        }

        $cantidadActual = $this->items[$productoId]['cantidad'] ?? 0;

        if (($cantidadActual + $cantidad) > $producto->stock_actual) {
            Notification::make()
                ->title('Stock insuficiente')
                ->body("Disponible: {$producto->stock_actual} | En carrito: {$cantidadActual}")
                ->danger()
                ->send();
            return;
        }

        if (isset($this->items[$productoId])) {
            $this->items[$productoId]['cantidad'] += $cantidad;
            $this->items[$productoId]['subtotal'] = $this->items[$productoId]['cantidad'] * $producto->precio_venta;
        } else {
            $this->items[$productoId] = [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'precio' => $producto->precio_venta,
                'cantidad' => $cantidad,
                'subtotal' => $producto->precio_venta * $cantidad,
            ];
        }

        $this->guardarCarrito();
        Notification::make()
            ->title("✅ {$producto->nombre} agregado")
            ->success()
            ->send();
    }

    public function actualizarCantidad(int $productoId, int $cantidad): void
    {
        if ($cantidad <= 0) {
            $this->eliminarProducto($productoId);
            return;
        }

        $producto = Producto::find($productoId);

        if ($producto && $cantidad > $producto->stock_actual) {
            Notification::make()
                ->title('Stock insuficiente')
                ->body("Máximo disponible: {$producto->stock_actual}")
                ->danger()
                ->send();
            return;
        }

        $this->items[$productoId]['cantidad'] = $cantidad;
        $this->items[$productoId]['subtotal'] = $cantidad * $this->items[$productoId]['precio'];

        $this->guardarCarrito();
    }

    public function eliminarProducto(int $productoId): void
    {
        unset($this->items[$productoId]);
        $this->guardarCarrito();
        Notification::make()->title('Producto eliminado')->info()->send();
    }

    public function limpiarCarrito(): void
    {
        $this->items = [];
        $this->cliente_id = null;
        $this->guardarCarrito();
        Notification::make()->title('Carrito limpiado')->info()->send();
    }

    private function guardarCarrito(): void
    {
        session()->put('carrito_ventas', $this->items);
    }

    public function procesarVenta(): void
    {
        if (empty($this->items)) {
            Notification::make()->title('El carrito está vacío')->warning()->send();
            return;
        }

        if (!$this->cliente_id) {
            Notification::make()->title('Selecciona un cliente')->warning()->send();
            return;
        }

        try {
            DB::transaction(function () {
                $subtotal = $this->total;
                $impuesto = round($subtotal * 0.13, 2);
                $total = round($subtotal + $impuesto, 2);

                $pedido = PedidoVenta::create([
                    'numero' => PedidoVenta::generarNumero(),
                    'cliente_id' => $this->cliente_id,
                    'user_id' => auth()->id(),
                    'fecha_pedido' => now(),
                    'estado' => 'entregado',
                    'subtotal' => $subtotal,
                    'impuesto' => $impuesto,
                    'total' => $total,
                    'moneda' => 'USD',
                    'canal_venta' => 'directo',
                ]);

                foreach ($this->items as $item) {
                    PedidoVentaItem::create([
                        'pedido_venta_id' => $pedido->id,
                        'producto_id' => $item['id'],
                        'cantidad' => $item['cantidad'],
                        'cantidad_despachada' => $item['cantidad'],
                        'precio_unitario' => $item['precio'],
                        'subtotal' => $item['subtotal'],
                    ]);

                    $producto = Producto::lockForUpdate()->find($item['id']);
                    $stockAnterior = $producto->stock_actual;
                    $stockNuevo = $stockAnterior - $item['cantidad'];

                    $producto->update(['stock_actual' => $stockNuevo]);

                    MovimientoInventario::create([
                        'numero' => MovimientoInventario::generarNumero(),
                        'producto_id' => $producto->id,
                        'user_id' => auth()->id(),
                        'tipo' => 'salida_venta',
                        'cantidad' => $item['cantidad'],
                        'stock_anterior' => $stockAnterior,
                        'stock_nuevo' => $stockNuevo,
                        'costo_unitario' => $item['precio'],
                        'costo_total' => $item['subtotal'],
                        'referencia_type' => PedidoVenta::class,
                        'referencia_id' => $pedido->id,
                        'motivo' => "Venta POS - {$pedido->numero}",
                        'fecha_movimiento' => now(),
                    ]);
                }

                Notification::make()
                    ->title('✅ Venta procesada')
                    ->body("N°: {$pedido->numero} · Total: $" . number_format($total, 2))
                    ->success()
                    ->persistent()
                    ->send();

                $this->limpiarCarrito();
            });
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Error al procesar la venta')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}