<?php

namespace App\Filament\Widgets;

use App\Models\InventarioAlmacen;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class AlertasInventario extends BaseWidget
{
    protected static ?string $heading = '📦 Inventario por Sucursal';
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(InventarioAlmacen::query()->with(['producto', 'almacen']))
            ->columns([
                Tables\Columns\TextColumn::make('producto.nombre')
                    ->label('Producto')
                    ->searchable(),
                Tables\Columns\TextColumn::make('almacen.nombre')
                    ->label('Sucursal')
                    ->badge(),
                Tables\Columns\TextColumn::make('stock_actual')
                    ->label('Stock')
                    ->numeric(3)
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock_minimo')
                    ->label('Mínimo'),
                Tables\Columns\TextColumn::make('stock_maximo')
                    ->label('Máximo'),
                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->getStateUsing(fn($record) => match(true) {
                        $record->stock_actual >= $record->stock_maximo => '🔴 SOBRESTOCK',
                        $record->stock_actual <= $record->stock_minimo => '🟡 STOCK BAJO',
                        default => '🟢 ÓPTIMO',
                    }),
            ]);
    }
}