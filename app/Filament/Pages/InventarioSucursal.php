<?php

namespace App\Filament\Pages;

use App\Models\InventarioAlmacen;
use App\Models\Almacen;
use Filament\Pages\Page;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;

class InventarioSucursal extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel = 'Mi Inventario';
    protected static ?string $title = 'Inventario de Mi Sucursal';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.pages.inventario-sucursal';

    public function table(Table $table): Table
    {
        $user = auth()->user();
        $almacenId = $user->almacen_id;

        return $table
            ->query(
                InventarioAlmacen::query()
                    ->with(['producto'])
                    ->where('almacen_id', $almacenId)
                    ->whereHas('producto', fn($q) => $q->where('estado', 'activo'))
            )
            ->columns([
                TextColumn::make('producto.codigo')
                    ->label('Código')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                    
                TextColumn::make('producto.nombre')
                    ->label('Producto')
                    ->searchable()
                    ->sortable()
                    ->description(fn($record) => $record->producto->categoria?->nombre ?? 'Sin categoría'),
                    
                TextColumn::make('stock_actual')
                    ->label('Stock Actual')
                    ->numeric(3)
                    ->sortable()
                    ->color(fn($record) => match(true) {
                        $record->stock_actual <= $record->stock_minimo => 'danger',
                        $record->stock_actual >= $record->stock_maximo => 'warning',
                        default => 'success',
                    }),
                    
                TextColumn::make('stock_minimo')
                    ->label('Stock Mínimo')
                    ->numeric(3)
                    ->sortable(),
                    
                TextColumn::make('stock_maximo')
                    ->label('Stock Máximo')
                    ->numeric(3)
                    ->sortable(),
                    
                BadgeColumn::make('estado')
                    ->label('Estado')
                    ->getStateUsing(fn($record) => match(true) {
                        $record->stock_actual <= 0 => 'SIN STOCK',
                        $record->stock_actual <= $record->stock_minimo => 'STOCK BAJO',
                        $record->stock_actual >= $record->stock_maximo => 'STOCK ALTO',
                        default => 'ÓPTIMO',
                    })
                    ->colors([
                        'danger' => 'SIN STOCK',
                        'warning' => 'STOCK BAJO',
                        'info' => 'STOCK ALTO',
                        'success' => 'ÓPTIMO',
                    ]),
                    
                TextColumn::make('producto.precio_venta')
                    ->label('Precio Venta')
                    ->money('USD')
                    ->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('estado')
                    ->label('Filtrar por Estado')
                    ->options([
                        'critico' => 'Stock Crítico',
                        'alto' => 'Stock Alto',
                        'optimo' => 'Óptimo',
                    ])
                    ->query(function ($query, $data) {
                        if ($data['value'] === 'critico') {
                            $query->whereColumn('stock_actual', '<=', 'stock_minimo');
                        } elseif ($data['value'] === 'alto') {
                            $query->whereColumn('stock_actual', '>=', 'stock_maximo');
                        } elseif ($data['value'] === 'optimo') {
                            $query->whereColumn('stock_actual', '>', 'stock_minimo')
                                ->whereColumn('stock_actual', '<', 'stock_maximo');
                        }
                    }),
                    
                \Filament\Tables\Filters\Filter::make('bajo_minimo')
                    ->label('Stock por debajo del mínimo')
                    ->query(fn($query) => $query->whereColumn('stock_actual', '<', 'stock_minimo')),
                    
                \Filament\Tables\Filters\Filter::make('sobre_maximo')
                    ->label('Stock por encima del máximo')
                    ->query(fn($query) => $query->whereColumn('stock_actual', '>', 'stock_maximo')),
            ])
            ->defaultSort('producto.nombre')
            ->paginated([15, 25, 50, 100])
            ->actions([
                \Filament\Tables\Actions\Action::make('ver_producto')
                    ->label('Ver')
                    ->icon('heroicon-m-eye')
                    //->url(fn($record) => route('filament.ventas.resources.productos.view', $record->producto_id))
                    ->openUrlInNewTab(),
            ]);
    }

    public function getSucursalActualProperty()
    {
        $user = auth()->user();
        if ($user && $user->almacen_id) {
            return Almacen::find($user->almacen_id);
        }
        return null;
    }

    public function getResumenProperty()
    {
        $user = auth()->user();
        $query = InventarioAlmacen::where('almacen_id', $user->almacen_id);
        
        return [
            'total_productos' => $query->count(),
            'stock_bajo' => (clone $query)->whereColumn('stock_actual', '<=', 'stock_minimo')->count(),
            'sin_stock' => (clone $query)->where('stock_actual', '<=', 0)->count(),
            'stock_alto' => (clone $query)->whereColumn('stock_actual', '>=', 'stock_maximo')->count(),
            'valor_inventario' => (clone $query)->with('producto')->get()->sum(fn($item) => $item->stock_actual * $item->producto->precio_compra),
        ];
    }
}