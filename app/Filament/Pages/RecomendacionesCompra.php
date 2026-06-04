<?php

namespace App\Filament\Pages;

use App\Filament\Resources\PedidoCompraResource;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class RecomendacionesCompra extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-light-bulb';
    protected static ?string $navigationLabel = 'Recomendaciones de Compra';
    protected static ?string $navigationGroup = 'Pedidos';
    protected static ?int    $navigationSort  = 2;
    protected static string  $view            = 'filament.pages.recomendaciones-compra';

    public array  $recomendaciones = [];
    public string $buscar          = '';

    public function mount(): void
    {
        $this->recomendaciones = PedidoCompraResource::generarRecomendaciones();
    }

    public function getRecomendacionesFiltradas(): array
    {
        if (trim($this->buscar) === '') {
            return $this->recomendaciones;
        }

        $termino = strtolower(trim($this->buscar));

        return collect($this->recomendaciones)
            ->filter(fn($r) =>
                str_contains(strtolower($r['producto']), $termino) ||
                str_contains(strtolower($r['proveedor']), $termino)
            )
            ->values()
            ->toArray();
    }

    public function refrescar(): void
    {
        $this->recomendaciones = PedidoCompraResource::generarRecomendaciones();
        $this->buscar          = '';
        Notification::make()->success()->title('Lista actualizada')->send();
    }

    public function getTotales(): array
    {
        $recs = $this->recomendaciones;

        return [
            'productos'   => count($recs),
            'sin_stock'   => collect($recs)->where('stock_actual', '<=', 0)->count(),
            'proveedores' => collect($recs)->pluck('proveedor')->unique()->count(),
            'costo_total' => collect($recs)->sum(fn($r) => $r['cant_sugerida'] * $r['precio']),
        ];
    }

    /**
     * Crea OC con un solo producto pre-cargado.
     */
    public function crearOCProducto(int $productoId, float $cantidad, int $proveedorId): void
    {
        $this->redirect(
            PedidoCompraResource::getUrl('create', [
                'producto_id'        => $productoId,
                'proveedor_id'       => $proveedorId,
                'cantidad_necesaria' => (int) ceil($cantidad),
            ])
        );
    }

    /**
     * Crea OC con TODOS los productos del proveedor pre-cargados.
     * Guarda los items en sesión para que CreatePedidoCompra los lea.
     */
    public function crearOCPorProveedor(string $proveedor): void
    {
        $items = collect($this->recomendaciones)
            ->where('proveedor', $proveedor)
            ->values();

        if ($items->isEmpty()) {
            Notification::make()->warning()->title('Sin productos para este proveedor')->send();
            return;
        }

        $proveedorId = $items->first()['proveedor_id'];

        session(['oc_items_proveedor' => $items->map(fn($r) => [
            'producto_id'     => $r['producto_id'],
            'cantidad'        => (int) ceil($r['cant_sugerida']),
            'precio_unitario' => (float) $r['precio'],
            'unidad_medida'   => $r['unidad_medida'] ?? 'unidad',
            'subtotal'        => round((int) ceil($r['cant_sugerida']) * (float) $r['precio'], 2),
        ])->toArray()]);

        $this->redirect(
            PedidoCompraResource::getUrl('create', [
                'proveedor_id' => $proveedorId,
                'from_session' => 1,
            ])
        );
    }
}