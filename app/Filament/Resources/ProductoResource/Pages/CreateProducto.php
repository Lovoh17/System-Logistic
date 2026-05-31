<?php

namespace App\Filament\Resources\ProductoResource\Pages;

use App\Filament\Resources\ProductoResource;
use App\Models\Almacen;
use App\Models\Producto;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;

class CreateProducto extends CreateRecord
{
    protected static string $resource = ProductoResource::class;

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identificación')
                ->icon('heroicon-o-identification')->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('codigo')
                        ->label('Código')
                        ->default(fn () => Producto::generarCodigo())
                        ->disabled()->dehydrated()->required()->columnSpan(1),
                    Forms\Components\TextInput::make('sku')
                        ->label('SKU')->unique(ignoreRecord: true)->maxLength(50)->columnSpan(1),
                    Forms\Components\Select::make('estado')
                        ->options([
                            'activo'        => 'Activo',
                            'inactivo'      => 'Inactivo',
                            'descontinuado' => 'Descontinuado',
                        ])
                        ->default('activo')->required()->columnSpan(1),
                    Forms\Components\TextInput::make('nombre')
                        ->label('Nombre del Producto')->required()->maxLength(150)->columnSpanFull(),
                    Forms\Components\Textarea::make('descripcion')
                        ->label('Descripción')->rows(3)->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Clasificación')
                ->icon('heroicon-o-tag')->columns(3)
                ->schema([
                    Forms\Components\Select::make('categoria_id')
                        ->label('Categoría')
                        ->relationship('categoria', 'nombre')->searchable()->preload()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('nombre')->required(),
                            Forms\Components\TextInput::make('slug')->required(),
                        ])->columnSpan(1),
                    Forms\Components\Select::make('proveedor_id')
                        ->label('Proveedor Principal')
                        ->relationship('proveedor', 'nombre')->searchable()->preload()->columnSpan(1),
                    Forms\Components\Select::make('unidad_medida')
                        ->label('Unidad de Medida')
                        ->options([
                            'unidad' => 'Unidad', 'kg' => 'Kilogramo (kg)', 'g' => 'Gramo (g)',
                            'litro'  => 'Litro (L)', 'ml' => 'Mililitro (ml)', 'caja' => 'Caja',
                            'palet'  => 'Palé', 'docena' => 'Docena', 'metro' => 'Metro (m)',
                        ])
                        ->default('unidad')->required()->columnSpan(1),
                ]),

            Forms\Components\Section::make('Precios')
                ->icon('heroicon-o-currency-dollar')->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('precio_compra')
                        ->label('Precio de Compra ($)')->numeric()->prefix('$')->default(0)->step(0.01)->columnSpan(1),
                    Forms\Components\TextInput::make('precio_venta')
                        ->label('Precio de Venta ($)')->numeric()->prefix('$')->default(0)->step(0.01)->columnSpan(1),
                    Forms\Components\Placeholder::make('margen')
                        ->label('Margen Estimado')
                        ->content(function (Forms\Get $get): string {
                            $compra = (float) $get('precio_compra');
                            $venta  = (float) $get('precio_venta');
                            if ($compra == 0) return '—';
                            $margen = round((($venta - $compra) / $compra) * 100, 2);
                            return "{$margen}%";
                        })->columnSpan(1),
                ]),

            Forms\Components\Section::make('Configuración de Stock por Sucursal')
                ->icon('heroicon-o-building-storefront')
                ->description('Configurar stock mínimo, máximo y punto de reorden para cada sucursal')
                ->schema([
                    Forms\Components\Repeater::make('inventarioAlmacen')
                        ->relationship('inventarioAlmacen')->label('')
                        ->schema([
                            Forms\Components\Select::make('almacen_id')
                                ->label('Sucursal')
                                ->options(Almacen::where('activo', true)->pluck('nombre', 'id'))
                                ->required()->searchable()->columnSpan(2),
                            Forms\Components\Grid::make(4)->schema([
                                Forms\Components\TextInput::make('stock_actual')
                                    ->label('Stock Actual')->numeric()->default(0)->step(0.001)
                                    ->disabled()->dehydrated()
                                    ->helperText('Solo lectura - se actualiza con movimientos')->columnSpan(1),
                                Forms\Components\TextInput::make('stock_minimo')
                                    ->label('Stock Mínimo')->numeric()->default(0)->step(0.001)->required()->columnSpan(1),
                                Forms\Components\TextInput::make('stock_maximo')
                                    ->label('Stock Máximo')->numeric()->default(0)->step(0.001)->required()->columnSpan(1),
                                Forms\Components\TextInput::make('punto_reorden')
                                    ->label('Punto de Reorden')->numeric()->default(0)->step(0.001)
                                    ->helperText('Nivel que activa alerta de reabastecimiento')->columnSpan(1),
                            ]),
                        ])
                        ->addActionLabel('Agregar configuración por sucursal')
                        ->defaultItems(1)->minItems(1)->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Información Adicional')
                ->icon('heroicon-o-information-circle')->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('ubicacion_almacen')
                        ->label('Ubicación en Almacén (Referencia)')
                        ->placeholder('Ej: A-01-03')->maxLength(50)->columnSpan(1),
                    Forms\Components\TextInput::make('peso_kg')
                        ->label('Peso (kg)')->numeric()->step(0.001)->columnSpan(1),
                    Forms\Components\Toggle::make('requiere_refrigeracion')
                        ->label('Requiere Refrigeración')->default(false)->columnSpan(1),
                    Forms\Components\Toggle::make('es_perecedero')
                        ->label('Es Perecedero')->default(false)->columnSpan(1),
                    Forms\Components\TextInput::make('vida_util_dias')
                        ->label('Vida Útil (días)')->numeric()->minValue(0)
                        ->visible(fn (Forms\Get $get) => $get('es_perecedero'))->columnSpan(1),
                    Forms\Components\FileUpload::make('imagen')
                        ->label('Imagen del Producto')->image()->directory('productos')->columnSpanFull(),
                ]),
        ]);
    }
}
