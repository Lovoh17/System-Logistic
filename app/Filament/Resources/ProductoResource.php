<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductoResource\Pages;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Categoria;
use App\Models\Almacen;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

// exports de excel
use App\Exports\InventarioSucursalExport;
use App\Exports\InventarioGeneralExport;
use Maatwebsite\Excel\Facades\Excel;

class ProductoResource extends Resource
{
    protected static ?string $model = Producto::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel = 'Productos';
    protected static ?string $navigationGroup = 'Inventario';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'Producto';
    protected static ?string $pluralModelLabel = 'Productos';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identificación')
                ->icon('heroicon-o-identification')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('codigo')
                        ->label('Código')
                        ->default(fn() => Producto::generarCodigo())
                        ->disabled()
                        ->dehydrated()
                        ->required()
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('sku')
                        ->label('SKU')
                        ->unique(ignoreRecord: true)
                        ->maxLength(50)
                        ->columnSpan(1),

                    Forms\Components\Select::make('estado')
                        ->options([
                            'activo'       => 'Activo',
                            'inactivo'     => 'Inactivo',
                            'descontinuado' => 'Descontinuado',
                        ])
                        ->default('activo')
                        ->required()
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('nombre')
                        ->label('Nombre del Producto')
                        ->required()
                        ->maxLength(150)
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('descripcion')
                        ->label('Descripción')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Clasificación')
                ->icon('heroicon-o-tag')
                ->columns(3)
                ->schema([
                    Forms\Components\Select::make('categoria_id')
                        ->label('Categoría')
                        ->relationship('categoria', 'nombre')
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('nombre')->required(),
                            Forms\Components\TextInput::make('slug')->required(),
                        ])
                        ->columnSpan(1),

                    Forms\Components\Select::make('proveedor_id')
                        ->label('Proveedor Principal')
                        ->relationship('proveedor', 'nombre')
                        ->searchable()
                        ->preload()
                        ->columnSpan(1),

                    Forms\Components\Select::make('unidad_medida')
                        ->label('Unidad de Medida')
                        ->options([
                            'unidad'  => 'Unidad',
                            'kg'      => 'Kilogramo (kg)',
                            'g'       => 'Gramo (g)',
                            'litro'   => 'Litro (L)',
                            'ml'      => 'Mililitro (ml)',
                            'caja'    => 'Caja',
                            'palet'   => 'Palé',
                            'docena'  => 'Docena',
                            'metro'   => 'Metro (m)',
                        ])
                        ->default('unidad')
                        ->required()
                        ->columnSpan(1),
                ]),

            Forms\Components\Section::make('Precios')
                ->icon('heroicon-o-currency-dollar')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('precio_compra')
                        ->label('Precio de Compra ($)')
                        ->numeric()
                        ->prefix('$')
                        ->default(0)
                        ->step(0.01)
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('precio_venta')
                        ->label('Precio de Venta ($)')
                        ->numeric()
                        ->prefix('$')
                        ->default(0)
                        ->step(0.01)
                        ->columnSpan(1),

                    Forms\Components\Placeholder::make('margen')
                        ->label('Margen Estimado')
                        ->content(function (Forms\Get $get): string {
                            $compra = (float) $get('precio_compra');
                            $venta  = (float) $get('precio_venta');
                            if ($compra == 0) return '—';
                            $margen = round((($venta - $compra) / $compra) * 100, 2);
                            return "{$margen}%";
                        })
                        ->columnSpan(1),
                ]),

            Forms\Components\Section::make('Configuración de Stock por Sucursal')
                ->icon('heroicon-o-building-storefront')
                ->description('Configurar stock mínimo, máximo y punto de reorden para cada sucursal')
                ->schema([
                    Forms\Components\Repeater::make('inventarioAlmacen')
                        ->relationship('inventarioAlmacen')
                        ->label('')
                        ->schema([
                            Forms\Components\Select::make('almacen_id')
                                ->label('Sucursal')
                                ->options(Almacen::where('activo', true)->pluck('nombre', 'id'))
                                ->required()
                                ->searchable()
                                ->columnSpan(2),

                            Forms\Components\Grid::make(4)
                                ->schema([
                                    Forms\Components\TextInput::make('stock_actual')
                                        ->label('Stock Actual')
                                        ->numeric()
                                        ->default(0)
                                        ->step(0.001)
                                        ->disabled()
                                        ->dehydrated()
                                        ->helperText('Solo lectura - se actualiza con movimientos')
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('stock_minimo')
                                        ->label('Stock Mínimo')
                                        ->numeric()
                                        ->default(0)
                                        ->step(0.001)
                                        ->required()
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('stock_maximo')
                                        ->label('Stock Máximo')
                                        ->numeric()
                                        ->default(0)
                                        ->step(0.001)
                                        ->required()
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('punto_reorden')
                                        ->label('Punto de Reorden')
                                        ->numeric()
                                        ->default(0)
                                        ->step(0.001)
                                        ->helperText('Nivel que activa alerta de reabastecimiento')
                                        ->columnSpan(1),
                                ]),
                        ])
                        ->addActionLabel('Agregar configuración por sucursal')
                        ->defaultItems(1)
                        ->minItems(1)
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Información Adicional')
                ->icon('heroicon-o-information-circle')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('ubicacion_almacen')
                        ->label('Ubicación en Almacén (Referencia)')
                        ->placeholder('Ej: A-01-03')
                        ->maxLength(50)
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('peso_kg')
                        ->label('Peso (kg)')
                        ->numeric()
                        ->step(0.001)
                        ->columnSpan(1),

                    Forms\Components\Toggle::make('requiere_refrigeracion')
                        ->label('Requiere Refrigeración')
                        ->default(false)
                        ->columnSpan(1),

                    Forms\Components\Toggle::make('es_perecedero')
                        ->label('Es Perecedero')
                        ->default(false)
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('vida_util_dias')
                        ->label('Vida Útil (días)')
                        ->numeric()
                        ->minValue(0)
                        ->visible(fn(Forms\Get $get) => $get('es_perecedero'))
                        ->columnSpan(1),

                    Forms\Components\FileUpload::make('imagen')
                        ->label('Imagen del Producto')
                        ->image()
                        ->directory('productos')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('imagen')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(url('/images/no-product.png'))
                    ->size(40),

                Tables\Columns\TextColumn::make('codigo')
                    ->label('Código')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('nombre')
                    ->label('Producto')
                    ->searchable()
                    ->sortable()
                    ->description(fn($record) => $record->categoria?->nombre),

                Tables\Columns\TextColumn::make('proveedor.nombre')
                    ->label('Proveedor')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('precio_venta')
                    ->label('Precio Venta')
                    ->money('USD')
                    ->sortable(),

                // ✅ Stock Total - sin sortable porque es un accesor
                Tables\Columns\TextColumn::make('stock_total')
                    ->label('Stock Total')
                    ->alignCenter()
                    ->badge()
                    ->color(fn($record) => $record->stock_color)
                    ->formatStateUsing(fn($record) => $record->stock_total . ' ' . $record->unidad_medida),

                // ✅ Stock por Sucursal (detalle)
                Tables\Columns\TextColumn::make('stock_por_sucursal')
                    ->label('Stock por Sucursal')
                    ->formatStateUsing(function ($record) {
                        $stocks = $record->inventarioAlmacen()
                            ->with('almacen')
                            ->get()
                            ->map(fn($inv) => "{$inv->almacen->nombre}: {$inv->stock_actual} {$record->unidad_medida}")
                            ->implode("\n");
                        return $stocks ?: 'Sin stock configurado';
                    })
                    ->html()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('requiere_refrigeracion')
                    ->label('❄️')
                    ->boolean()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('estado')
                    ->badge()
                    ->colors([
                        'success' => 'activo',
                        'gray'    => 'inactivo',
                        'danger'  => 'descontinuado',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->options([
                        'activo'       => 'Activo',
                        'inactivo'     => 'Inactivo',
                        'descontinuado' => 'Descontinuado',
                    ]),
                Tables\Filters\Filter::make('stock_bajo')
                    ->label('Stock Bajo (alguna sucursal)')
                    ->query(fn(Builder $query) => $query->whereHas('inventarioAlmacen', function($q) {
                        $q->whereColumn('stock_actual', '<=', 'stock_minimo');
                    })),
                Tables\Filters\Filter::make('sin_stock')
                    ->label('Sin Stock (todas las sucursales)')
                    ->query(fn(Builder $query) => $query->whereDoesntHave('inventarioAlmacen', function($q) {
                        $q->where('stock_actual', '>', 0);
                    })),
                Tables\Filters\SelectFilter::make('categoria_id')
                    ->label('Categoría')
                    ->relationship('categoria', 'nombre'),
                Tables\Filters\SelectFilter::make('proveedor_id')
                    ->label('Proveedor')
                    ->relationship('proveedor', 'nombre'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('ver_stock')
                ->label('Ver Stock')
                ->icon('heroicon-o-chart-bar')
                ->color('info')
                ->modalHeading(fn($record) => "Stock de {$record->nombre}")
                ->modalContent(function ($record) {
                    $stocks = $record->inventarioAlmacen()
                        ->with('almacen')
                        ->get();
                    
                    return view('filament.Modals.ver-stock', [
                        'stocks' => $stocks,
                        'record' => $record,
                    ]);
                })
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Cerrar'),
                        ])
                        ->headerActions([
                            Tables\Actions\Action::make('exportar_general')
                                ->label('Exportar Inventario General')
                                ->icon('heroicon-m-document-arrow-down')
                                ->color('success')
                                ->action(function () {
                                    return Excel::download(new InventarioGeneralExport(), 'inventario_general_' . date('Y-m-d') . '.xlsx');
                                }),
                            Tables\Actions\Action::make('exportar_por_sucursal')
                                ->label('Exportar por Sucursal')
                                ->icon('heroicon-m-document-arrow-down')
                                ->color('info')
                                ->action(function () {
                                    return Excel::download(new InventarioSucursalExport(), 'inventario_por_sucursal_' . date('Y-m-d') . '.xlsx');
                                }),
                        ])
                        ->bulkActions([
                            Tables\Actions\BulkActionGroup::make([
                                Tables\Actions\DeleteBulkAction::make(),
                                Tables\Actions\BulkAction::make('actualizar_stock_minimo')
                                    ->label('Actualizar Stock Mínimo')
                                    ->icon('heroicon-m-pencil')
                                    ->form([
                                        Forms\Components\TextInput::make('stock_minimo')
                                            ->label('Nuevo Stock Mínimo')
                                            ->numeric()
                                            ->required()
                                            ->step(0.001),
                                        Forms\Components\Select::make('almacen_id')
                                            ->label('Aplicar a sucursal')
                                            ->options(Almacen::where('activo', true)->pluck('nombre', 'id'))
                                            ->placeholder('Todas las sucursales'),
                                    ])
                                    ->action(function (Collection $records, array $data) {
                                        foreach ($records as $record) {
                                            $query = $record->inventarioAlmacen();
                                            if (isset($data['almacen_id']) && $data['almacen_id']) {
                                                $query->where('almacen_id', $data['almacen_id']);
                                            }
                                            $query->update(['stock_minimo' => $data['stock_minimo']]);
                                        }
                                    }),
                            ]),
                        ])
                        ->defaultSort('codigo', 'asc');
    }

    public static function getNavigationBadge(): ?string
    {
        // Productos con stock total = 0 en todas las sucursales
        $sinStock = static::getModel()::whereDoesntHave('inventarioAlmacen', function($q) {
            $q->where('stock_actual', '>', 0);
        })->count();
        
        return $sinStock > 0 ? (string) $sinStock : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Productos sin stock en todas las sucursales';
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProducto::route('/'),
            'create' => Pages\CreateProducto::route('/create'),
            'edit'   => Pages\EditProducto::route('/{record}/edit'),
        ];
    }
}