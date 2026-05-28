<?php

namespace App\Filament\Contador\Pages;

use App\Models\PedidoCompra;
use App\Models\PedidoVenta;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class EstadoResultados extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon  = 'heroicon-o-scale';
    protected static ?string $navigationLabel = 'Estado de Resultados';
    protected static ?string $title           = 'Estado de Resultados';
    protected static ?string $navigationGroup = 'Finanzas';
    protected static ?int    $navigationSort  = 2;
    protected static string  $view            = 'filament.contador.pages.estado-resultados';

    public ?array $data = [];

    // Resultados calculados (públicos para acceso en la vista)
    public float $ingresos         = 0;
    public float $costoVentas      = 0;
    public float $utilidadBruta    = 0;
    public float $gastosOperativos = 0;
    public float $utilidadNeta     = 0;
    public float $margenBruto      = 0;
    public float $margenNeto       = 0;
    public int   $totalPedidosVenta  = 0;
    public int   $totalPedidosCompra = 0;

    public function mount(): void
    {
        $this->form->fill([
            'fecha_inicio' => now()->startOfMonth()->toDateString(),
            'fecha_fin'    => now()->toDateString(),
        ]);
        $this->calcular();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Período de Análisis')
                    ->icon('heroicon-o-calendar-days')
                    ->schema([
                        DatePicker::make('fecha_inicio')
                            ->label('Desde')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y'),

                        DatePicker::make('fecha_fin')
                            ->label('Hasta')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->afterOrEqual('fecha_inicio'),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function calcular(): void
    {
        $inicio = $this->data['fecha_inicio'] ?? now()->startOfMonth()->toDateString();
        $fin    = $this->data['fecha_fin']    ?? now()->toDateString();

        // Ingresos: ventas entregadas/confirmadas
        $this->ingresos = (float) PedidoVenta::whereDate('fecha_pedido', '>=', $inicio)
            ->whereDate('fecha_pedido', '<=', $fin)
            ->whereNotIn('estado', ['cancelado', 'borrador'])
            ->sum('total');

        $this->totalPedidosVenta = PedidoVenta::whereDate('fecha_pedido', '>=', $inicio)
            ->whereDate('fecha_pedido', '<=', $fin)
            ->whereNotIn('estado', ['cancelado', 'borrador'])
            ->count();

        // Costo de ventas: qty * precio_compra del producto
        $this->costoVentas = (float) DB::table('pedidos_venta_items as pvi')
            ->join('pedidos_venta as pv', 'pv.id', '=', 'pvi.pedido_venta_id')
            ->join('productos as p', 'p.id', '=', 'pvi.producto_id')
            ->whereDate('pv.fecha_pedido', '>=', $inicio)
            ->whereDate('pv.fecha_pedido', '<=', $fin)
            ->whereNotIn('pv.estado', ['cancelado', 'borrador'])
            ->whereNull('pv.deleted_at')
            ->sum(DB::raw('pvi.cantidad * p.precio_compra'));

        // Gastos operativos: compras del período
        $this->gastosOperativos = (float) PedidoCompra::whereDate('fecha_pedido', '>=', $inicio)
            ->whereDate('fecha_pedido', '<=', $fin)
            ->whereNotIn('estado', ['cancelado', 'borrador'])
            ->sum('total');

        $this->totalPedidosCompra = PedidoCompra::whereDate('fecha_pedido', '>=', $inicio)
            ->whereDate('fecha_pedido', '<=', $fin)
            ->whereNotIn('estado', ['cancelado', 'borrador'])
            ->count();

        // Utilidades
        $this->utilidadBruta = $this->ingresos - $this->costoVentas;
        $this->utilidadNeta  = $this->utilidadBruta - $this->gastosOperativos;

        $this->margenBruto = $this->ingresos > 0
            ? round(($this->utilidadBruta / $this->ingresos) * 100, 2)
            : 0;

        $this->margenNeto = $this->ingresos > 0
            ? round(($this->utilidadNeta / $this->ingresos) * 100, 2)
            : 0;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('calcular')
                ->label('Recalcular')
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->action('calcular'),
        ];
    }
}