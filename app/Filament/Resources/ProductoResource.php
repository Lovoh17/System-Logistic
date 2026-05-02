<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductoResource\Pages;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Categoria;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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

            Forms\Components\Section::make('Control de Stock')
                ->icon('heroicon-o-chart-bar')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('stock_actual')
                        ->label('Stock Actual')
                        ->numeric()
                        ->default(0)
                        ->disabled()
                        ->dehydrated()
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('stock_minimo')
                        ->label('Stock Mínimo (Alerta)')
                        ->numeric()
                        ->default(0)
                        ->step(0.001)
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('stock_maximo')
                        ->label('Stock Máximo')
                        ->numeric()
                        ->default(0)
                        ->step(0.001)
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('ubicacion_almacen')
                        ->label('Ubicación en Almacén')
                        ->placeholder('Ej: A-01-03'  )
                        ->maxLength(50)
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('peso_kg')
                        ->label('Peso (kg)')
                        ->numeric()
                        ->step(0.001)
                        ->columnSpan(1),
                ]),

            Forms\Components\Section::make('Atributos Especiales')
                ->icon('heroicon-o-exclamation-triangle')
                ->columns(3)
                ->schema([
                    Forms\Components\Toggle::make('requiere_refrigeracion')
                        ->label('Requiere Refrigeración')
                        ->default(false),

                    Forms\Components\Toggle::make('es_perecedero')
                        ->label('Es Perecedero')
                        ->default(false),

                    Forms\Components\TextInput::make('vida_util_dias')
                        ->label('Vida Útil (días)')
                        ->numeric()
                        ->minValue(0)
                        ->visible(fn (Forms\Get $get) => $get('es_perecedero')),

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

                Tables\Columns\TextColumn::make('stock_actual')
                    ->label('Stock')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color(fn($record) => $record->stock_color)
                    ->formatStateUsing(fn($state, $record) => $state . ' ' . $record->unidad_medida),

                Tables\Columns\IconColumn::make('requiere_refrigeracion')
                    ->label('❄️')
                    ->boolean()
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('estado')
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
                    ->label('Stock Bajo')
                    ->query(fn (Builder $query) => $query->whereColumn('stock_actual', '<=', 'stock_minimo')),
                Tables\Filters\Filter::make('sin_stock')
                    ->label('Sin Stock')
                    ->query(fn (Builder $query) => $query->where('stock_actual', '<=', 0)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        $sinStock = static::getModel()::sinStock()->count();
        return $sinStock > 0 ? (string) $sinStock : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Productos sin stock';
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
