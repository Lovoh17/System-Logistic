<?php

namespace App\Filament\Widgets;

use App\Models\Producto;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class StockCriticoWidget extends BaseWidget
{
    protected static ?string $heading = '⚠️ Productos con Stock Crítico';
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Producto::query()
                    ->whereColumn('stock_actual', '<=', 'stock_minimo')
                    ->where('estado', 'activo')
                    ->orderBy('stock_actual')
                    ->limit(8)
            )
            ->columns([
                Tables\Columns\TextColumn::make('codigo')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('nombre')
                    ->limit(25),
                Tables\Columns\TextColumn::make('stock_actual')
                    ->label('Stock')
                    ->badge()
                    ->color(fn($record) => $record->stock_color),
                Tables\Columns\TextColumn::make('stock_minimo')
                    ->label('Mín.'),
                Tables\Columns\TextColumn::make('proveedor.nombre')
                    ->label('Proveedor')
                    ->limit(15),
            ])
            ->actions([
                Tables\Actions\Action::make('comprar')
                    ->label('OC')
                    ->url(fn($record) => route('filament.admin.resources.pedido-compras.create'))
                    ->icon('heroicon-m-shopping-bag')
                    ->color('warning'),
            ]);
    }
}