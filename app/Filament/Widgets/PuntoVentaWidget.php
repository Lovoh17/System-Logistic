<?php

namespace App\Filament\Widgets;

use App\Models\Producto;
use App\Models\Cliente;
use App\Models\PedidoVenta;
use App\Models\PedidoVentaItem;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Session;

class PuntoVentaWidget extends Widget
{
    protected static ?string $heading = '🛒 Punto de Venta';
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';
    
    protected static string $view = 'filament.widgets.punto-venta-widget';
    
    public $carrito = [];
    public $cliente_id = null;
    public $producto_id = null;
    public $cantidad = 1;
    public $busqueda = '';
    
    public function mount()
    {
        $this->carrito = Session::get('carrito', []);
    }
    
    public function agregarProducto()
    {
        $producto = Producto::find($this->producto_id);
        
        if (!$producto) {
            return;
        }
        
        if ($this->cantidad > $producto->stock_actual) {
            \Filament\Notifications\Notification::make()
                ->title('Stock insuficiente')
                ->body("Solo hay {$producto->stock_actual} unidades disponibles")
                ->danger()
                ->send();
            return;
        }
        
        if (isset($this->carrito[$producto->id])) {
            $nuevaCantidad = $this->carrito[$producto->id]['cantidad'] + $this->cantidad;
            if ($nuevaCantidad > $producto->stock_actual) {
                \Filament\Notifications\Notification::make()
                    ->title('Stock insuficiente')
                    ->danger()
                    ->send();
                return;
            }
            $this->carrito[$producto->id]['cantidad'] = $nuevaCantidad;
        } else {
            $this->carrito[$producto->id] = [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'precio' => $producto->precio_venta,
                'cantidad' => $this->cantidad,
                'subtotal' => $producto->precio_venta * $this->cantidad,
            ];
        }
        
        $this->actualizarCarrito();
        $this->producto_id = null;
        $this->cantidad = 1;
        
        \Filament\Notifications\Notification::make()
            ->title('Producto agregado')
            ->success()
            ->send();
    }
    
    public function eliminarProducto($productoId)
    {
        unset($this->carrito[$productoId]);
        $this->actualizarCarrito();
    }
    
    public function actualizarCantidad($productoId, $cantidad)
    {
        $producto = Producto::find($productoId);
        if ($cantidad > $producto->stock_actual) {
            \Filament\Notifications\Notification::make()
                ->title('Stock insuficiente')
                ->danger()
                ->send();
            return;
        }
        
        if ($cantidad <= 0) {
            $this->eliminarProducto($productoId);
            return;
        }
        
        $this->carrito[$productoId]['cantidad'] = $cantidad;
        $this->carrito[$productoId]['subtotal'] = $this->carrito[$productoId]['precio'] * $cantidad;
        $this->actualizarCarrito();
    }
    
    public function actualizarCarrito()
    {
        Session::put('carrito', $this->carrito);
        $this->dispatch('carrito-actualizado');
    }
    
    public function getTotalProperty()
    {
        return collect($this->carrito)->sum('subtotal');
    }
    
    public function getItemsCountProperty()
    {
        return collect($this->carrito)->sum('cantidad');
    }
    
    public function limpiarCarrito()
    {
        $this->carrito = [];
        $this->actualizarCarrito();
    }
    
    public function procesarVenta()
    {
        if (empty($this->carrito)) {
            \Filament\Notifications\Notification::make()
                ->title('Carrito vacío')
                ->warning()
                ->send();
            return;
        }
        
        if (!$this->cliente_id) {
            \Filament\Notifications\Notification::make()
                ->title('Seleccione un cliente')
                ->warning()
                ->send();
            return;
        }
        
        try {
            // Crear pedido de venta
            $pedido = PedidoVenta::create([
                'numero' => PedidoVenta::generarNumero(),
                'cliente_id' => $this->cliente_id,
                'user_id' => auth()->id(),
                'fecha_pedido' => now(),
                'estado' => 'confirmado',
                'subtotal' => $this->total,
                'impuesto' => $this->total * 0.13,
                'total' => $this->total * 1.13,
                'moneda' => 'USD',
            ]);
            
            // Crear items del pedido
            foreach ($this->carrito as $item) {
                PedidoVentaItem::create([
                    'pedido_venta_id' => $pedido->id,
                    'producto_id' => $item['id'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio'],
                    'subtotal' => $item['subtotal'],
                ]);
                
                // Actualizar stock
                $producto = Producto::find($item['id']);
                $producto->stock_actual -= $item['cantidad'];
                $producto->save();
            }
            
            $this->limpiarCarrito();
            $this->cliente_id = null;
            
            \Filament\Notifications\Notification::make()
                ->title('Venta procesada')
                ->body("Pedido N°: {$pedido->numero} | Total: $" . number_format($pedido->total, 2))
                ->success()
                ->send();
                
        } catch (\Exception $e) {
            \Filament\Notifications\Notification::make()
                ->title('Error al procesar venta')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    public function getProductosBusquedaProperty()
    {
        if (empty($this->busqueda)) {
            return [];
        }
        
        return Producto::where('estado', 'activo')
            ->where('nombre', 'like', "%{$this->busqueda}%")
            ->orWhere('codigo', 'like', "%{$this->busqueda}%")
            ->orWhere('sku', 'like', "%{$this->busqueda}%")
            ->limit(10)
            ->get();
    }
    
    public function seleccionarProducto($productoId)
    {
        $this->producto_id = $productoId;
        $this->busqueda = '';
        $this->agregarProducto();
    }
}