<?php

namespace App\Filament\Widgets;

use App\Models\Producto;
use App\Models\Cliente;
use App\Models\PedidoVenta;
use App\Models\PedidoVentaItem;
use App\Models\InventarioAlmacen;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class PuntoVentaWidget extends Widget
{
    protected static ?string $heading = '🛒 Punto de Venta';
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';
    
    protected static string $view = 'filament.ventas.widgets.punto-venta-widget';
    
    public $carrito = [];
    public $cliente_id = null;
    public $producto_id = null;
    public $cantidad = 1;
    public $busqueda = '';
    
    public function mount()
    {
        $this->carrito = session()->get('carrito_ventas', []);
    }
    
    public function agregarProducto()
    {
        $producto = Producto::find($this->producto_id);
        
        if (!$producto) {
            return;
        }
        
        if ($this->cantidad > $producto->stock_actual) {
            Notification::make()
                ->title('Stock insuficiente')
                ->body("Solo hay {$producto->stock_actual} unidades disponibles")
                ->danger()
                ->send();
            return;
        }
        
        if (isset($this->carrito[$producto->id])) {
            $nuevaCantidad = $this->carrito[$producto->id]['cantidad'] + $this->cantidad;
            if ($nuevaCantidad > $producto->stock_actual) {
                Notification::make()->title('Stock insuficiente')->danger()->send();
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
        $this->busqueda = '';
        
        Notification::make()->title('Producto agregado')->success()->send();
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
            Notification::make()->title('Stock insuficiente')->danger()->send();
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
        session()->put('carrito_ventas', $this->carrito);
        $this->dispatch('carrito-actualizado');
    }
    
    public function getTotalProperty()
    {
        return collect($this->carrito)->sum('subtotal');
    }
    
    public function getTotalConIvaProperty()
    {
        return $this->total * 1.13;
    }
    
    public function getIvaProperty()
    {
        return $this->total * 0.13;
    }
    
    public function limpiarCarrito()
    {
        $this->carrito = [];
        $this->actualizarCarrito();
        Notification::make()->title('Carrito limpiado')->info()->send();
    }
    
    public function procesarVenta()
    {
        if (empty($this->carrito)) {
            Notification::make()->title('Carrito vacío')->warning()->send();
            return;
        }
        
        if (!$this->cliente_id) {
            Notification::make()->title('Seleccione un cliente')->warning()->send();
            return;
        }
        
        try {
            DB::beginTransaction();
            
            $subtotal = $this->total;
            $impuesto = $subtotal * 0.13;
            $total = $subtotal + $impuesto;
            
            $pedido = PedidoVenta::create([
                'numero' => PedidoVenta::generarNumero(),
                'cliente_id' => $this->cliente_id,
                'user_id' => auth()->id(),
                'fecha_pedido' => now(),
                'estado' => 'confirmado',
                'subtotal' => $subtotal,
                'impuesto' => $impuesto,
                'total' => $total,
                'moneda' => 'USD',
            ]);
            
            foreach ($this->carrito as $item) {
                PedidoVentaItem::create([
                    'pedido_venta_id' => $pedido->id,
                    'producto_id' => $item['id'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio'],
                    'subtotal' => $item['subtotal'],
                ]);
                
                $producto = Producto::find($item['id']);
                $producto->stock_actual -= $item['cantidad'];
                $producto->save();
            }
            
            DB::commit();
            
            $this->mostrarTicket($pedido);
            $this->limpiarCarrito();
            $this->cliente_id = null;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Notification::make()->title('Error al procesar venta')->body($e->getMessage())->danger()->send();
        }
    }
    
    public function mostrarTicket($pedido)
    {
        $pedido->load(['cliente', 'items.producto']);
        
        $html = view('filament.ventas.widgets.ticket-venta', compact('pedido'))->render();
        
        $this->dispatch('open-ticket-modal', html: $html);
    }
    
    public function getProductosBusquedaProperty()
    {
        if (empty($this->busqueda) || strlen($this->busqueda) < 2) {
            return collect();
        }
        
        return Producto::where('estado', 'activo')
            ->where(function($q) {
                $q->where('nombre', 'like', "%{$this->busqueda}%")
                  ->orWhere('codigo', 'like', "%{$this->busqueda}%")
                  ->orWhere('sku', 'like', "%{$this->busqueda}%");
            })
            ->limit(10)
            ->get();
    }
    
    public function getClientesProperty()
    {
        return Cliente::where('estado', 'activo')->orderBy('nombre')->get();
    }
    
    public function getProductosRapidosProperty()
    {
        return Producto::where('estado', 'activo')
            ->where('stock_actual', '>', 0)
            ->orderBy('stock_actual', 'desc')
            ->limit(8)
            ->get();
    }
}