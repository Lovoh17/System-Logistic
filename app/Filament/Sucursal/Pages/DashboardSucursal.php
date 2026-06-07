<?php

namespace App\Filament\Sucursal\Pages;

use App\Models\InventarioAlmacen;
use App\Models\PedidoVenta;
use App\Models\Traslado;
use Filament\Pages\Page;

class DashboardSucursal extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?string $title           = 'Mi Sucursal';
    protected static ?int    $navigationSort  = 1;
    protected static ?string $navigationGroup = 'Mi Sucursal';
    protected static string  $view            = 'filament.sucursal.pages.dashboard-sucursal';

    private function almacenId(): ?int
    {
        return auth()->user()?->almacen_id;
    }

    public function getVentasMes(): array
    {
        $id    = $this->almacenId();
        $inicio = now()->startOfMonth();
        $fin    = now()->endOfMonth();

        $query = PedidoVenta::where('almacen_id', $id)
            ->whereBetween('fecha_pedido', [$inicio, $fin])
            ->whereNotIn('estado', ['cancelado']);

        return [
            'total' => (float) $query->sum('total'),
            'count' => $query->count(),
        ];
    }

    public function getStockCritico(): int
    {
        return InventarioAlmacen::where('almacen_id', $this->almacenId())
            ->whereColumn('stock_actual', '<=', 'stock_minimo')
            ->count();
    }

    public function getTrasladosPendientes(): int
    {
        $id = $this->almacenId();
        return Traslado::where(fn($q) => $q
            ->where('almacen_origen_id', $id)
            ->orWhere('almacen_destino_id', $id))
            ->whereIn('estado', ['sugerido', 'aprobado'])
            ->count();
    }

    public function getPedidosPendientes(): int
    {
        return PedidoVenta::where('almacen_id', $this->almacenId())
            ->whereIn('estado', ['confirmado', 'en_preparacion'])
            ->count();
    }

    public function getUltimasVentas()
    {
        return PedidoVenta::with('cliente')
            ->where('almacen_id', $this->almacenId())
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();
    }

    public function getUltimosTrasladosSucursal()
    {
        $id = $this->almacenId();
        return Traslado::with(['almacenOrigen', 'almacenDestino'])
            ->where(fn($q) => $q
                ->where('almacen_origen_id', $id)
                ->orWhere('almacen_destino_id', $id))
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();
    }
}
