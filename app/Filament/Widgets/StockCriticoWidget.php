<?php

namespace App\Filament\Widgets;

use App\Models\Producto;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class StockCriticoWidget extends BaseWidget
{
    protected static ?string $heading = 'Productos con Stock Crítico';
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Producto::query()
                    ->where('estado', 'activo')
                    ->whereHas('inventarioAlmacen', function (Builder $query) {
                        $query->whereColumn('stock_actual', '<=', 'stock_minimo');
                    })
                    ->with(['inventarioAlmacen' => function ($query) {
                        $query->whereColumn('stock_actual', '<=', 'stock_minimo')
                              ->with('almacen');
                    }])
                    ->with('proveedor')
                    ->limit(8)
            )
            ->columns([
                Tables\Columns\TextColumn::make('codigo')
                    ->label('Código')
                    ->badge()
                    ->color('gray')
                    ->searchable(),

                Tables\Columns\TextColumn::make('nombre')
                    ->label('Producto')
                    ->limit(25)
                    ->searchable(),

                Tables\Columns\TextColumn::make('stock_critico_info')
                    ->label('Stock Actual / Mínimo')
                    ->formatStateUsing(function ($record) {
                        $stocksCriticos = $record->inventarioAlmacen
                            ->filter(fn($inv) => $inv->stock_actual <= $inv->stock_minimo);
                        
                        if ($stocksCriticos->isEmpty()) {
                            return '—';
                        }
                        
                        $info = [];
                        foreach ($stocksCriticos as $inv) {
                            $info[] = "{$inv->almacen->nombre}: {$inv->stock_actual} / {$inv->stock_minimo}";
                        }
                        
                        return implode(' | ', $info);
                    })
                    ->html()
                    ->tooltip(function ($record) {
                        $stocksCriticos = $record->inventarioAlmacen
                            ->filter(fn($inv) => $inv->stock_actual <= $inv->stock_minimo);
                        
                        $info = [];
                        foreach ($stocksCriticos as $inv) {
                            $info[] = "{$inv->almacen->nombre}: {$inv->stock_actual} (mínimo: {$inv->stock_minimo})";
                        }
                        
                        return implode("\n", $info);
                    }),

                Tables\Columns\TextColumn::make('stock_total_calculado')
                    ->label('Stock Total')
                    ->badge()
                    ->color(fn($record) => $record->stock_color)
                    ->formatStateUsing(function ($record) {
                        $total = $record->inventarioAlmacen->sum('stock_actual');
                        return $total . ' ' . $record->unidad_medida;
                    }),

                Tables\Columns\TextColumn::make('proveedor.nombre')
                    ->label('Proveedor')
                    ->limit(15)
                    ->toggleable(),
            ])
            ->actions([
                Tables\Actions\Action::make('ver_stock')
                    ->label('Ver Stock')
                    ->icon('heroicon-m-eye')
                    ->color('info')
                    ->url(fn ($record) => \App\Filament\Resources\ProductoResource::getUrl('view', ['record' => $record])),

                Tables\Actions\Action::make('comprar')
                    ->label('OC')
                    ->url(fn($record) => route('filament.admin.resources.pedido-compras.create'))
                    ->icon('heroicon-m-shopping-bag')
                    ->color('warning'),
            ])
            ->emptyStateHeading('No hay productos con stock crítico')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->emptyStateDescription('Todos los productos tienen stock por encima del mínimo')
            ->defaultSort('codigo', 'asc');
    }

    // Título dinámico
    public function getHeading(): string
    {
        $count = Producto::where('estado', 'activo')
            ->whereHas('inventarioAlmacen', function ($query) {
                $query->whereColumn('stock_actual', '<=', 'stock_minimo');
            })
            ->count();
        
        return "⚠️ Productos con Stock Crítico ({$count})";
    }
}