<?php

namespace App\Filament\Pages;

use App\Models\Envio;
use App\Models\PedidoVenta;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Cliente;
use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms;
use Illuminate\Support\Carbon;

class ReporteLogistico extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon  = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Reportes Logísticos';
    protected static ?string $navigationGroup = 'Administración';
    protected static ?int    $navigationSort  = 10;
    protected static string  $view = 'filament.pages.reporte-logistico';

    public ?string $periodo = 'mes_actual';
    public ?string $fecha_desde = null;
    public ?string $fecha_hasta = null;

    public function mount(): void
    {
        $this->fecha_desde = now()->startOfMonth()->format('Y-m-d');
        $this->fecha_hasta = now()->endOfMonth()->format('Y-m-d');
    }

    public function getMetricas(): array
    {
        $desde = Carbon::parse($this->fecha_desde)->startOfDay();
        $hasta = Carbon::parse($this->fecha_hasta)->endOfDay();

        // ── Pedidos de Venta ──────────────────────────
        $pedidos = PedidoVenta::whereBetween('fecha_pedido', [$desde, $hasta]);

        $totalVentas    = $pedidos->clone()->whereNotIn('estado', ['cancelado', 'borrador'])->sum('total');
        $pedidosTotales = $pedidos->clone()->count();
        $pedidosEntregados = $pedidos->clone()->where('estado', 'entregado')->count();
        $pedidosCancelados = $pedidos->clone()->where('estado', 'cancelado')->count();

        $tasaEntrega = $pedidosTotales > 0
            ? round(($pedidosEntregados / $pedidosTotales) * 100, 1)
            : 0;

        // ── Envíos ────────────────────────────────────
        $envios       = Envio::whereBetween('fecha_programada', [$desde, $hasta]);
        $totalEnvios  = $envios->clone()->count();
        $enviosEntregados = $envios->clone()->where('estado', 'entregado')->count();
        $enviosFallidos   = $envios->clone()->where('estado', 'fallido')->count();

        // Tiempo promedio de entrega
        $tiempoPromedio = Envio::whereBetween('fecha_programada', [$desde, $hasta])
            ->where('estado', 'entregado')
            ->whereNotNull('fecha_salida')
            ->whereNotNull('fecha_entrega_real')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, fecha_salida, fecha_entrega_real)) as promedio')
            ->value('promedio');

        // ── Inventario ────────────────────────────────
        $productosStockBajo  = Producto::whereColumn('stock_actual', '<=', 'stock_minimo')->count();
        $productosSinStock   = Producto::where('stock_actual', '<=', 0)->count();
        $valorInventario     = Producto::selectRaw('SUM(stock_actual * precio_compra) as valor')->value('valor') ?? 0;

        // ── Proveedores ───────────────────────────────
        $proveedoresActivos = Proveedor::where('estado', 'activo')->count();

        // ── Top clientes ──────────────────────────────
        $topClientes = PedidoVenta::whereBetween('fecha_pedido', [$desde, $hasta])
            ->whereNotIn('estado', ['cancelado', 'borrador'])
            ->with('cliente')
            ->selectRaw('cliente_id, COUNT(*) as num_pedidos, SUM(total) as total_comprado')
            ->groupBy('cliente_id')
            ->orderByDesc('total_comprado')
            ->limit(5)
            ->get();

        // ── Top productos vendidos ─────────────────────
        $topProductos = \App\Models\PedidoVentaItem::whereHas('pedidoVenta', fn($q) =>
                $q->whereBetween('fecha_pedido', [$desde, $hasta])
                  ->whereNotIn('estado', ['cancelado', 'borrador'])
            )
            ->with('producto')
            ->selectRaw('producto_id, SUM(cantidad) as total_vendido, SUM(subtotal) as total_valor')
            ->groupBy('producto_id')
            ->orderByDesc('total_vendido')
            ->limit(5)
            ->get();

        return compact(
            'totalVentas', 'pedidosTotales', 'pedidosEntregados', 'pedidosCancelados',
            'tasaEntrega', 'totalEnvios', 'enviosEntregados', 'enviosFallidos',
            'tiempoPromedio', 'productosStockBajo', 'productosSinStock',
            'valorInventario', 'proveedoresActivos', 'topClientes', 'topProductos'
        );
    }

    public function getTitle(): string
    {
        return 'Reportes Logísticos';
    }
}
