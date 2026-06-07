<?php

namespace App\Filament\Sucursal\Resources;

use App\Filament\Sucursal\Resources\InventarioSucursalResource\Pages;
use App\Models\InventarioAlmacen;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InventarioSucursalResource extends Resource
{
    protected static ?string $model = InventarioAlmacen::class;

    protected static ?string $navigationIcon   = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel  = 'Mi Inventario';
    protected static ?string $navigationGroup  = 'Mi Sucursal';
    protected static ?int    $navigationSort   = 2;
    protected static ?string $modelLabel       = 'Inventario';
    protected static ?string $pluralModelLabel = 'Inventario de Mi Sucursal';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('almacen_id', auth()->user()?->almacen_id)
            ->with(['producto', 'almacen']);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Parámetros de Stock')->columns(3)->schema([
                Forms\Components\TextInput::make('stock_minimo')
                    ->label('Stock Mínimo')->numeric()->step(0.001)->minValue(0),
                Forms\Components\TextInput::make('stock_maximo')
                    ->label('Stock Máximo')->numeric()->step(0.001)->minValue(0),
                Forms\Components\TextInput::make('punto_reorden')
                    ->label('Punto de Reorden')->numeric()->step(0.001)->minValue(0),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('producto.codigo')
                    ->label('Código')->searchable()->sortable()->badge()->color('gray'),

                Tables\Columns\TextColumn::make('producto.nombre')
                    ->label('Producto')->searchable()->sortable()
                    ->description(fn($record) => $record->producto?->categoria?->nombre),

                Tables\Columns\TextColumn::make('stock_actual')
                    ->label('Stock Actual')->numeric(2)->sortable()
                    ->color(fn($record) => match(true) {
                        $record->stock_actual <= $record->stock_minimo => 'danger',
                        $record->stock_actual >= $record->stock_maximo => 'warning',
                        default => 'success',
                    })
                    ->formatStateUsing(fn($state, $record) =>
                        number_format($state, 2) . ' ' . ($record->producto?->unidad_medida ?? 'u')),

                Tables\Columns\TextColumn::make('stock_minimo')
                    ->label('Mínimo')
                    ->formatStateUsing(fn($state, $record) =>
                        number_format($state, 2) . ' ' . ($record->producto?->unidad_medida ?? 'u')),

                Tables\Columns\TextColumn::make('stock_maximo')
                    ->label('Máximo')->toggleable()
                    ->formatStateUsing(fn($state, $record) =>
                        number_format($state, 2) . ' ' . ($record->producto?->unidad_medida ?? 'u')),

                Tables\Columns\BadgeColumn::make('nivel')
                    ->label('Nivel')
                    ->getStateUsing(fn($record) => match(true) {
                        $record->stock_actual <= $record->stock_minimo => 'Bajo',
                        $record->stock_actual >= $record->stock_maximo => 'Excedente',
                        default                                        => 'Óptimo',
                    })
                    ->colors(['danger' => 'Bajo', 'warning' => 'Excedente', 'success' => 'Óptimo']),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('nivel_stock')
                    ->label('Estado de Stock')
                    ->options(['bajo' => 'Stock Bajo', 'optimo' => 'Óptimo', 'excedente' => 'Excedente'])
                    ->query(fn($query, $data) => match($data['value'] ?? null) {
                        'bajo'      => $query->whereColumn('stock_actual', '<=', 'stock_minimo'),
                        'excedente' => $query->whereColumn('stock_actual', '>=', 'stock_maximo'),
                        'optimo'    => $query->whereColumn('stock_actual', '>', 'stock_minimo')
                                             ->whereColumn('stock_actual', '<', 'stock_maximo'),
                        default     => $query,
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Ajustar Parámetros'),
            ])
            ->defaultSort('stock_actual', 'asc');
    }

    public static function getNavigationBadge(): ?string
    {
        $id = auth()->user()?->almacen_id;
        if (!$id) return null;
        $critico = InventarioAlmacen::where('almacen_id', $id)
            ->whereColumn('stock_actual', '<=', 'stock_minimo')->count();
        return $critico > 0 ? (string) $critico : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventarioSucursal::route('/'),
            'edit'  => Pages\EditInventarioSucursal::route('/{record}/edit'),
        ];
    }
}
