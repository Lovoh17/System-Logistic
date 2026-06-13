<?php

namespace App\Filament\Pages\Ventas;

use App\Jobs\EnviarReciboVenta;
use App\Models\Almacen;
use App\Models\Cliente;
use App\Models\InventarioAlmacen;
use App\Models\PedidoVenta;
use App\Services\VentaService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Log;

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

    public function getSucursalActualProperty()
    {
        $user = auth()->user();
        if ($user && $user->almacen_id) {
            return Almacen::find($user->almacen_id);
        }

        return null;
    }

    public function getClientesProperty()
    {
        return Cliente::where('estado', 'activo')->orderBy('nombre')->get();
    }

    public function getProductosProperty()
    {
        $user = auth()->user();

        if (empty($this->searchTerm) || strlen($this->searchTerm) < 2) {
            return collect();
        }

        $query = InventarioAlmacen::query()
            ->with(['producto'])
            ->where('almacen_id', $user->almacen_id)
            ->where('stock_actual', '>', 0)
            ->whereHas('producto', function ($q) {
                $q->where('estado', 'activo')
                    ->where(function ($sq) {
                        $sq->where('nombre', 'like', "%{$this->searchTerm}%")
                            ->orWhere('codigo', 'like', "%{$this->searchTerm}%")
                            ->orWhere('sku', 'like', "%{$this->searchTerm}%");
                    });
            });

        return $query->limit(20)->get();
    }

    // Productos destacados (también filtrados por sucursal)
    public function getProductosRapidosProperty()
    {
        $user = auth()->user();

        return InventarioAlmacen::query()
            ->with(['producto'])
            ->where('almacen_id', $user->almacen_id)
            ->where('stock_actual', '>', 0)
            ->whereHas('producto', fn ($q) => $q->where('estado', 'activo'))
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

    public function agregarProducto(int $productoId, int $cantidad = 1): void
    {
        $user = auth()->user();

        $inventario = InventarioAlmacen::where('producto_id', $productoId)
            ->where('almacen_id', $user->almacen_id)
            ->with(['producto'])
            ->first();

        if (! $inventario) {
            Notification::make()->title('Producto no disponible en esta sucursal')->danger()->send();

            return;
        }

        $producto = $inventario->producto;
        $stockDisponible = $inventario->stock_actual;

        if ($cantidad > $stockDisponible) {
            Notification::make()
                ->title('Stock insuficiente')
                ->body("Disponible: {$stockDisponible} unidades")
                ->danger()
                ->send();

            return;
        }

        $cantidadActual = $this->items[$productoId]['cantidad'] ?? 0;

        if (($cantidadActual + $cantidad) > $stockDisponible) {
            Notification::make()
                ->title('Stock insuficiente')
                ->body("Disponible: {$stockDisponible} | En carrito: {$cantidadActual}")
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
        $this->searchTerm = '';
        Notification::make()
            ->title("{$producto->nombre} agregado")
            ->success()
            ->send();
    }

    public function actualizarCantidad(int $productoId, int $cantidad): void
    {
        if ($cantidad <= 0) {
            $this->eliminarProducto($productoId);

            return;
        }

        $user = auth()->user();
        $inventario = InventarioAlmacen::where('producto_id', $productoId)
            ->where('almacen_id', $user->almacen_id)
            ->first();

        if ($inventario && $cantidad > $inventario->stock_actual) {
            Notification::make()
                ->title('Stock insuficiente')
                ->body("Máximo disponible: {$inventario->stock_actual}")
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

        if (! $this->cliente_id) {
            Notification::make()->title('Selecciona un cliente')->warning()->send();

            return;
        }

        $subtotal = $this->total;
        $impuesto = round($subtotal * 0.13, 2);
        $total = round($subtotal + $impuesto, 2);
        $itemsVenta = $this->items;
        $almacenId = auth()->user()->almacen_id;

        try {
            $pedido = app(VentaService::class)->procesarVentaPos(
                $this->cliente_id,
                $almacenId,
                $itemsVenta,
                auth()->id(),
            );

            // Recibo por correo: encolado y fuera de la transacción para no bloquear la caja.
            $this->encolarRecibo($pedido, $subtotal, $impuesto, $total, $itemsVenta);

            Notification::make()
                ->title('Venta procesada')
                ->body("N°: {$pedido->numero} · Total: $".number_format($total, 2))
                ->success()
                ->persistent()
                ->send();

            $this->limpiarCarrito();
        } catch (\Throwable $e) {
            Log::error('Error al procesar venta: '.$e->getMessage());
            Notification::make()
                ->title('Error al procesar la venta')
                ->body('No se pudo completar la venta. Intenta nuevamente o contacta al administrador.')
                ->danger()
                ->send();
        }
    }

    /**
     * Encola el envío del recibo al cliente (si tiene email válido).
     *
     * @param  array<int, array<string, mixed>>  $itemsVenta
     */
    private function encolarRecibo(PedidoVenta $pedido, float $subtotal, float $impuesto, float $total, array $itemsVenta): void
    {
        $cliente = Cliente::find($this->cliente_id);

        if (! $cliente || ! $cliente->email || ! filter_var($cliente->email, FILTER_VALIDATE_EMAIL)) {
            Notification::make()
                ->title('Recibo no enviado')
                ->body('El cliente no tiene un email válido registrado.')
                ->warning()
                ->send();

            return;
        }

        EnviarReciboVenta::dispatch($cliente->email, $cliente->nombre, [
            'numero' => $pedido->numero,
            'fecha' => $pedido->fecha_pedido->format('d/m/Y H:i'),
            'cliente_nombre' => $cliente->nombre,
            'sucursal' => $this->sucursalActual?->nombre ?? 'No especificada',
            'items' => $itemsVenta,
            'subtotal' => $subtotal,
            'impuesto' => $impuesto,
            'total' => $total,
            'metodo_pago' => 'Efectivo',
        ]);

        Notification::make()
            ->title('Recibo en cola de envío')
            ->body("Se enviará el recibo a {$cliente->email}.")
            ->success()
            ->send();
    }
}
