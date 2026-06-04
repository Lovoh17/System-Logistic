<?php

namespace App\Filament\Contador\Pages;

use App\Exports\CuentasPorCobrarExport;
use App\Exports\InventarioValorizadoExport;
use App\Exports\VentasPeriodoExport;
use App\Models\PedidoVenta;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;

class ReportesContador extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon  = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationLabel = 'Reportes';
    protected static ?string $title           = 'Reportes Financieros';
    protected static ?string $navigationGroup = 'Reportes';
    protected static ?int    $navigationSort  = 1;
    protected static string  $view            = 'filament.contador.pages.reportes-contador';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'fecha_inicio' => now()->startOfMonth()->toDateString(),
            'fecha_fin'    => now()->toDateString(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Filtros de Período')
                    ->icon('heroicon-o-calendar')
                    ->schema([
                        DatePicker::make('fecha_inicio')
                            ->label('Desde')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->columnSpan(1),

                        DatePicker::make('fecha_fin')
                            ->label('Hasta')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->afterOrEqual('fecha_inicio')
                            ->columnSpan(1),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => PedidoVenta::query()
                ->with(['cliente', 'almacen', 'user'])
                ->when(
                    $this->data['fecha_inicio'] ?? null,
                    fn ($q, $d) => $q->whereDate('fecha_pedido', '>=', $d)
                )
                ->when(
                    $this->data['fecha_fin'] ?? null,
                    fn ($q, $d) => $q->whereDate('fecha_pedido', '<=', $d)
                )
                ->whereNotIn('estado', ['borrador'])
                ->orderByDesc('fecha_pedido')
            )
            ->columns([
                TextColumn::make('numero')
                    ->label('N° Pedido')
                    ->searchable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('cliente.nombre')
                    ->label('Cliente')
                    ->searchable()
                    ->limit(25),

                TextColumn::make('almacen.nombre')
                    ->label('Sucursal')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('fecha_pedido')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),

                BadgeColumn::make('estado')
                    ->label('Estado')
                    ->colors([
                        'success' => 'entregado',
                        'info'    => 'confirmado',
                        'warning' => 'en_preparacion',
                        'danger'  => 'cancelado',
                        'gray'    => 'borrador',
                    ]),

                TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('impuesto')
                    ->label('IVA')
                    ->money('USD'),

                TextColumn::make('total')
                    ->label('Total')
                    ->money('USD')
                    ->sortable()
                    ->weight('bold'),
            ])
            ->filters([
                SelectFilter::make('estado')
                    ->options([
                        'confirmado'     => 'Confirmado',
                        'en_preparacion' => 'En Preparación',
                        'entregado'      => 'Entregado',
                        'cancelado'      => 'Cancelado',
                    ]),

                SelectFilter::make('almacen_id')
                    ->label('Sucursal')
                    ->relationship('almacen', 'nombre'),
            ])
            ->defaultSort('fecha_pedido', 'desc')
            ->paginated([15, 25, 50, 100])
            ->striped();
    }

    public function aplicarFiltros(): void
    {
        $this->form->validate();
        $this->resetTable();
        Notification::make()
            ->title('Filtros aplicados')
            ->success()
            ->send();
    }

    public function exportarVentasExcel(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $this->form->validate();
        $inicio = $this->data['fecha_inicio'];
        $fin    = $this->data['fecha_fin'];

        return Excel::download(
            new VentasPeriodoExport($inicio, $fin),
            "ventas_{$inicio}_{$fin}.xlsx"
        );
    }

    public function exportarInventarioExcel(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return Excel::download(
            new InventarioValorizadoExport(),
            'inventario_valorizado_' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function exportarCuentasExcel(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return Excel::download(
            new CuentasPorCobrarExport(),
            'cuentas_por_cobrar_' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportar_ventas')
                ->label('Exportar Ventas')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action('exportarVentasExcel'),

            Action::make('exportar_inventario')
                ->label('Inventario Valorizado')
                ->icon('heroicon-o-archive-box')
                ->color('info')
                ->action('exportarInventarioExcel'),

            Action::make('exportar_cuentas')
                ->label('Cuentas por Cobrar')
                ->icon('heroicon-o-clipboard-document-list')
                ->color('warning')
                ->action('exportarCuentasExcel'),
        ];
    }
}