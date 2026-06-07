<?php

namespace App\Filament\Sucursal\Pages;

use App\Models\InventarioAlmacen;
use App\Models\MovimientoInventario;
use App\Models\PedidoVenta;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportesSucursal extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon  = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Reportes';
    protected static ?string $title           = 'Reportes de Mi Sucursal';
    protected static ?int    $navigationSort  = 1;
    protected static ?string $navigationGroup = 'Reportes';
    protected static string  $view            = 'filament.sucursal.pages.reportes-sucursal';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'fecha_inicio'   => now()->startOfMonth()->toDateString(),
            'fecha_fin'      => now()->toDateString(),
            'tipo_reporte'   => 'ventas',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Filtros')
                ->columns(3)
                ->schema([
                    DatePicker::make('fecha_inicio')
                        ->label('Desde')
                        ->required(),

                    DatePicker::make('fecha_fin')
                        ->label('Hasta')
                        ->required()
                        ->afterOrEqual('fecha_inicio'),

                    Select::make('tipo_reporte')
                        ->label('Tipo de Reporte')
                        ->options([
                            'ventas'   => 'Ventas de la Sucursal',
                            'salidas'  => 'Salidas de Productos',
                            'ingresos' => 'Ingresos de Productos',
                        ])
                        ->required()
                        ->live(),
                ]),
        ])->statePath('data');
    }

    public function table(Table $table): Table
    {
        $almacenId   = auth()->user()?->almacen_id;
        $tipo        = $this->data['tipo_reporte'] ?? 'ventas';
        $fechaInicio = $this->data['fecha_inicio'] ?? null;
        $fechaFin    = $this->data['fecha_fin']    ?? null;

        if ($tipo === 'ventas') {
            return $table
                ->query(fn(): Builder => PedidoVenta::query()
                    ->with(['cliente'])
                    ->where('almacen_id', $almacenId)
                    ->when($fechaInicio, fn($q, $d) => $q->whereDate('fecha_pedido', '>=', $d))
                    ->when($fechaFin,    fn($q, $d) => $q->whereDate('fecha_pedido', '<=', $d))
                    ->whereNotIn('estado', ['borrador'])
                    ->orderByDesc('fecha_pedido')
                )
                ->columns([
                    TextColumn::make('numero')
                        ->label('N° Pedido')->searchable()->badge()->color('primary'),
                    TextColumn::make('cliente.nombre')
                        ->label('Cliente')->searchable()->limit(25),
                    TextColumn::make('fecha_pedido')
                        ->label('Fecha')->date('d/m/Y')->sortable(),
                    BadgeColumn::make('estado')->colors([
                        'gray'    => 'borrador',
                        'info'    => 'confirmado',
                        'warning' => 'en_preparacion',
                        'primary' => 'listo',
                        'success' => 'entregado',
                        'danger'  => 'cancelado',
                    ]),
                    TextColumn::make('subtotal')->label('Subtotal')->money('USD')->sortable(),
                    TextColumn::make('impuesto')->label('IVA')->money('USD'),
                    TextColumn::make('total')->label('Total')->money('USD')->sortable()->weight('bold'),
                ])
                ->filters([
                    SelectFilter::make('estado')->options([
                        'confirmado'     => 'Confirmado',
                        'en_preparacion' => 'En Preparación',
                        'entregado'      => 'Entregado',
                        'cancelado'      => 'Cancelado',
                    ]),
                ])
                ->defaultSort('fecha_pedido', 'desc')
                ->paginated([10, 25, 50])
                ->striped();
        }

        // Salidas e ingresos — tabla de movimientos
        $tiposMovimiento = $tipo === 'salidas'
            ? ['salida_venta', 'salida', 'traslado_salida', 'merma', 'ajuste_negativo', 'devolucion_compra']
            : ['entrada_compra', 'traslado_entrada', 'devolucion_venta', 'ajuste_positivo', 'inventario_inicial'];

        return $table
            ->query(fn(): Builder => MovimientoInventario::query()
                ->with(['producto', 'user'])
                ->where('almacen_id', $almacenId)
                ->whereIn('tipo', $tiposMovimiento)
                ->when($fechaInicio, fn($q, $d) => $q->whereDate('fecha_movimiento', '>=', $d))
                ->when($fechaFin,    fn($q, $d) => $q->whereDate('fecha_movimiento', '<=', $d))
                ->orderByDesc('fecha_movimiento')
            )
            ->columns([
                TextColumn::make('numero')
                    ->label('N° Movimiento')->searchable()->badge()->color('primary'),
                TextColumn::make('producto.nombre')
                    ->label('Producto')->searchable()->limit(30),
                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'entrada_compra', 'traslado_entrada',
                        'devolucion_venta', 'ajuste_positivo',
                        'inventario_inicial' => 'success',
                        default              => 'danger',
                    })
                    ->formatStateUsing(fn($state) => ucfirst(str_replace('_', ' ', $state))),
                TextColumn::make('cantidad')
                    ->label('Cantidad')->numeric()->sortable(),
                TextColumn::make('costo_unitario')
                    ->label('C. Unit.')->money('USD'),
                TextColumn::make('costo_total')
                    ->label('Costo Total')->money('USD')->sortable()->weight('bold'),
                TextColumn::make('fecha_movimiento')
                    ->label('Fecha')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('user.name')
                    ->label('Registrado por')->limit(20)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('fecha_movimiento', 'desc')
            ->paginated([10, 25, 50])
            ->striped();
    }

    public function aplicarFiltros(): void
    {
        $this->form->validate();
        $this->resetPage();
        Notification::make()->success()->title('Filtros aplicados')->send();
    }

    // ── Exportar Excel ───────────────────────────────────

    public function exportarExcel(): BinaryFileResponse
    {
        $this->form->validate();

        $tipo      = $this->data['tipo_reporte'] ?? 'ventas';
        $inicio    = $this->data['fecha_inicio'];
        $fin       = $this->data['fecha_fin'];
        $almacenId = auth()->user()?->almacen_id;
        $nombre    = "reporte_{$tipo}_{$inicio}_{$fin}.xlsx";

        $datos    = $this->getDatosReporte($tipo, $inicio, $fin, $almacenId);
        $cabecera = $this->getCabecera($tipo);

        return Excel::download(
            new class($datos, $cabecera) implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize {
                public function __construct(
                    private array $datos,
                    private array $cabecera
                ) {}

                public function collection()
                {
                    return collect($this->datos);
                }

                public function headings(): array
                {
                    return $this->cabecera;
                }

                public function styles(Worksheet $sheet)
                {
                    return [
                        1 => [
                            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                            'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '1a7a5e']],
                            'alignment' => ['horizontal' => 'center'],
                        ],
                    ];
                }
            },
            $nombre
        );
    }

    // ── Exportar PDF ─────────────────────────────────────

    public function exportarPdf(): StreamedResponse
    {
        $this->form->validate();

        $tipo      = $this->data['tipo_reporte'] ?? 'ventas';
        $inicio    = $this->data['fecha_inicio'];
        $fin       = $this->data['fecha_fin'];
        $almacenId = auth()->user()?->almacen_id;
        $almacen   = auth()->user()?->almacen;

        $datos    = $this->getDatosReporte($tipo, $inicio, $fin, $almacenId);
        $cabecera = $this->getCabecera($tipo);

        $titulos = [
            'ventas'   => 'Reporte de Ventas',
            'salidas'  => 'Reporte de Salidas de Productos',
            'ingresos' => 'Reporte de Ingresos de Productos',
        ];

        $pdf = Pdf::loadHTML($this->generarHtmlPdf(
            titulo:   $titulos[$tipo] ?? 'Reporte',
            sucursal: $almacen?->nombre ?? 'Sucursal',
            inicio:   $inicio,
            fin:      $fin,
            cabecera: $cabecera,
            datos:    $datos,
        ))->setPaper('letter', 'landscape');

        $nombre = "reporte_{$tipo}_{$inicio}_{$fin}.pdf";

        return response()->streamDownload(
            fn() => print($pdf->output()),
            $nombre,
            ['Content-Type' => 'application/pdf']
        );
    }

    // ── Helpers de datos ─────────────────────────────────

    private function getDatosReporte(string $tipo, ?string $inicio, ?string $fin, ?int $almacenId): array
    {
        if ($tipo === 'ventas') {
            return PedidoVenta::query()
                ->with('cliente')
                ->where('almacen_id', $almacenId)
                ->when($inicio, fn($q, $d) => $q->whereDate('fecha_pedido', '>=', $d))
                ->when($fin,    fn($q, $d) => $q->whereDate('fecha_pedido', '<=', $d))
                ->whereNotIn('estado', ['borrador'])
                ->orderByDesc('fecha_pedido')
                ->get()
                ->map(fn($v) => [
                    $v->numero,
                    $v->cliente?->nombre ?? '—',
                    $v->fecha_pedido?->format('d/m/Y') ?? '—',
                    ucfirst(str_replace('_', ' ', $v->estado)),
                    '$' . number_format($v->subtotal, 2),
                    '$' . number_format($v->impuesto, 2),
                    '$' . number_format($v->total, 2),
                ])
                ->toArray();
        }

        $tiposMovimiento = $tipo === 'salidas'
            ? ['salida_venta', 'salida', 'traslado_salida', 'merma', 'ajuste_negativo', 'devolucion_compra']
            : ['entrada_compra', 'traslado_entrada', 'devolucion_venta', 'ajuste_positivo', 'inventario_inicial'];

        return MovimientoInventario::query()
            ->with(['producto', 'user'])
            ->where('almacen_id', $almacenId)
            ->whereIn('tipo', $tiposMovimiento)
            ->when($inicio, fn($q, $d) => $q->whereDate('fecha_movimiento', '>=', $d))
            ->when($fin,    fn($q, $d) => $q->whereDate('fecha_movimiento', '<=', $d))
            ->orderByDesc('fecha_movimiento')
            ->get()
            ->map(fn($m) => [
                $m->numero,
                $m->producto?->nombre ?? '—',
                ucfirst(str_replace('_', ' ', $m->tipo)),
                $m->cantidad,
                '$' . number_format($m->costo_unitario ?? 0, 2),
                '$' . number_format($m->costo_total    ?? 0, 2),
                $m->fecha_movimiento?->format('d/m/Y H:i') ?? '—',
                $m->user?->name ?? '—',
            ])
            ->toArray();
    }

    private function getCabecera(string $tipo): array
    {
        return match($tipo) {
            'ventas'   => ['N° Pedido', 'Cliente', 'Fecha', 'Estado', 'Subtotal', 'IVA', 'Total'],
            'salidas'  => ['N° Movimiento', 'Producto', 'Tipo', 'Cantidad', 'C. Unitario', 'Costo Total', 'Fecha', 'Registrado por'],
            'ingresos' => ['N° Movimiento', 'Producto', 'Tipo', 'Cantidad', 'C. Unitario', 'Costo Total', 'Fecha', 'Registrado por'],
            default    => [],
        };
    }

    private function generarHtmlPdf(
        string $titulo,
        string $sucursal,
        string $inicio,
        string $fin,
        array  $cabecera,
        array  $datos,
    ): string {
        $filas = '';
        foreach ($datos as $fila) {
            $celdas = '';
            foreach ($fila as $celda) {
                $celdas .= "<td style='padding:5px 8px;border:1px solid #e5e7eb;font-size:11px;'>" . htmlspecialchars((string) $celda) . "</td>";
            }
            $filas .= "<tr>{$celdas}</tr>";
        }

        $encabezados = '';
        foreach ($cabecera as $col) {
            $encabezados .= "<th style='padding:7px 8px;background:#1a7a5e;color:#fff;font-size:11px;text-align:left;border:1px solid #1a7a5e;'>" . htmlspecialchars($col) . "</th>";
        }

        $generado = now()->format('d/m/Y H:i');

        return "
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='utf-8'>
            <style>
                body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; margin: 0; padding: 20px; }
                h1 { font-size: 16px; font-weight: bold; color: #1a7a5e; margin: 0 0 4px; }
                .meta { font-size: 11px; color: #6b7280; margin-bottom: 16px; }
                table { width: 100%; border-collapse: collapse; margin-top: 8px; }
                tr:nth-child(even) td { background: #f9fafb; }
                .footer { margin-top: 20px; font-size: 10px; color: #9ca3af; text-align: right; }
                .total-row td { font-weight: bold; background: #f3f4f6 !important; border-top: 2px solid #1a7a5e; }
            </style>
        </head>
        <body>
            <h1>{$titulo}</h1>
            <p class='meta'>
                Sucursal: <strong>{$sucursal}</strong> &nbsp;|&nbsp;
                Período: <strong>{$inicio}</strong> al <strong>{$fin}</strong> &nbsp;|&nbsp;
                Generado: <strong>{$generado}</strong>
            </p>
            <table>
                <thead><tr>{$encabezados}</tr></thead>
                <tbody>{$filas}</tbody>
            </table>
            <p class='footer'>Sistema Logístico — {$generado}</p>
        </body>
        </html>";
    }

    // ── KPIs ─────────────────────────────────────────────

    public function getResumenInventario(): array
    {
        $id = auth()->user()?->almacen_id;
        return [
            'total'     => InventarioAlmacen::where('almacen_id', $id)->count(),
            'critico'   => InventarioAlmacen::where('almacen_id', $id)
                ->where('stock_minimo', '>', 0)
                ->whereColumn('stock_actual', '<', 'stock_minimo')
                ->count(),
            'excedente' => InventarioAlmacen::where('almacen_id', $id)
                ->where('stock_maximo', '>', 0)
                ->whereColumn('stock_actual', '>', 'stock_maximo')
                ->count(),
        ];
    }

    public function getTopProductos(): array
    {
        $id = auth()->user()?->almacen_id;
        return MovimientoInventario::where('almacen_id', $id)
            ->whereIn('tipo', ['salida_venta', 'salida'])
            ->join('productos', 'productos.id', '=', 'movimientos_inventario.producto_id')
            ->selectRaw('productos.nombre as producto_nombre, SUM(movimientos_inventario.cantidad) as total_vendido')
            ->groupBy('productos.id', 'productos.nombre')
            ->orderByDesc('total_vendido')
            ->limit(5)
            ->get()
            ->map(fn($r) => [
                'producto' => $r->producto_nombre,
                'cantidad' => round((float) $r->total_vendido, 2),
            ])->toArray();
    }
}