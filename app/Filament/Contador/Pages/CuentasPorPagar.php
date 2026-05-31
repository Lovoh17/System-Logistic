<?php

namespace App\Filament\Contador\Pages;

use App\Models\Proveedor;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CuentasPorPagar extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon  = 'heroicon-o-truck';
    protected static ?string $navigationLabel = 'Cuentas por Pagar';
    protected static ?string $title           = 'Cuentas por Pagar';
    protected static ?string $navigationGroup = 'Cuentas';
    protected static ?int    $navigationSort  = 2;
    protected static string  $view            = 'filament.contador.pages.cuentas-por-pagar';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Proveedor::query()
                ->where('estado', 'activo')
                ->withCount([
                    'pedidosCompra as pedidos_pendientes_count' => fn ($q) =>
                        $q->whereNotIn('estado', ['recibido', 'cancelado', 'borrador']),
                ])
                ->withSum([
                    'pedidosCompra as monto_pendiente' => fn ($q) =>
                        $q->whereNotIn('estado', ['recibido', 'cancelado', 'borrador']),
                ], 'total')
                ->orderByDesc('monto_pendiente')
            )
            ->columns([
                TextColumn::make('codigo')
                    ->label('Código')
                    ->searchable()
                    ->badge()->color('gray'),

                TextColumn::make('nombre')
                    ->label('Proveedor')
                    ->searchable()->sortable()
                    ->weight('semibold'),

                TextColumn::make('razon_social')
                    ->label('Razón Social')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('ruc')
                    ->label('RUC/NIT')
                    ->toggleable(),

                TextColumn::make('pedidos_pendientes_count')
                    ->label('OC Pendientes')
                    ->alignCenter()
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'warning' : 'success'),

                TextColumn::make('monto_pendiente')
                    ->label('Monto Pendiente')
                    ->money('USD')
                    ->sortable()
                    ->weight('bold')
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'success'),

                TextColumn::make('tiempo_entrega_dias')
                    ->label('Días Entrega')
                    ->suffix(' días')
                    ->alignCenter()
                    ->toggleable(),

                TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->icon('heroicon-m-phone')
                    ->toggleable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->icon('heroicon-m-envelope')
                    ->copyable()
                    ->toggleable(),
            ])
            ->filters([
                Filter::make('con_deuda')
                    ->label('Solo con deuda pendiente')
                    ->query(fn ($q) => $q->whereHas('pedidosCompra', fn ($q) =>
                        $q->whereNotIn('estado', ['recibido', 'cancelado', 'borrador'])
                    ))
                    ->default(),
            ])
            ->defaultSort('monto_pendiente', 'desc')
            ->paginated([15, 25, 50])
            ->striped()
            ->poll('60s');
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
