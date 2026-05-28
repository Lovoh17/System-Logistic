<?php

namespace App\Filament\Contador\Pages;

use App\Exports\CuentasPorCobrarExport;
use App\Models\Cliente;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class CuentasPorCobrar extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon  = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Cuentas por Cobrar';
    protected static ?string $title           = 'Cuentas por Cobrar';
    protected static ?string $navigationGroup = 'Cuentas';
    protected static ?int    $navigationSort  = 1;
    protected static string  $view            = 'filament.contador.pages.cuentas-por-cobrar';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Cliente::query()
                ->where('limite_credito', '>', 0)
                ->withCount([
                    'pedidosVenta as pedidos_pendientes_count' => function ($q) {
                        $q->whereNotIn('estado', ['entregado', 'cancelado', 'borrador']);
                    },
                ])
                ->withSum([
                    'pedidosVenta as monto_pendiente' => function ($q) {
                        $q->whereNotIn('estado', ['entregado', 'cancelado', 'borrador']);
                    },
                ], 'total')
                ->orderByDesc('monto_pendiente')
            )
            ->columns([
                TextColumn::make('codigo')
                    ->label('Código')
                    ->searchable()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('nombre')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->colors([
                        'info'    => 'mayorista',
                        'success' => 'minorista',
                        'warning' => 'corporativo',
                    ]),

                TextColumn::make('limite_credito')
                    ->label('Límite Crédito')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('dias_credito')
                    ->label('Días Crédito')
                    ->suffix(' días')
                    ->alignCenter(),

                TextColumn::make('pedidos_pendientes_count')
                    ->label('Pedidos Pendientes')
                    ->alignCenter()
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'warning' : 'success'),

                TextColumn::make('monto_pendiente')
                    ->label('Monto Pendiente')
                    ->money('USD')
                    ->sortable()
                    ->weight('bold')
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'success'),

                TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->icon('heroicon-m-phone'),

                TextColumn::make('email')
                    ->label('Email')
                    ->icon('heroicon-m-envelope')
                    ->copyable(),
            ])
            ->filters([
                Filter::make('con_deuda')
                    ->label('Solo con deuda pendiente')
                    ->query(fn ($q) => $q->whereHas('pedidosVenta', function ($q) {
                        $q->whereNotIn('estado', ['entregado', 'cancelado', 'borrador']);
                    }))
                    ->default(),
            ])
            ->defaultSort('monto_pendiente', 'desc')
            ->paginated([15, 25, 50])
            ->striped()
            ->poll('60s');
    }

    public function exportarExcel(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return Excel::download(
            new CuentasPorCobrarExport(),
            'cuentas_por_cobrar_' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportar')
                ->label('Exportar Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action('exportarExcel'),
        ];
    }
}