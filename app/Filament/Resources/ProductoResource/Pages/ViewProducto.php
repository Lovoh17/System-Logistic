<?php

namespace App\Filament\Resources\ProductoResource\Pages;

use App\Filament\Resources\ProductoResource;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewProducto extends ViewRecord
{
    protected static string $resource = ProductoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Identificación')
                ->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('codigo')
                        ->badge()->color('gray'),
                    Infolists\Components\TextEntry::make('sku')
                        ->label('SKU'),
                    Infolists\Components\TextEntry::make('estado')
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'activo'        => 'success',
                            'inactivo'      => 'gray',
                            'descontinuado' => 'danger',
                            default         => 'gray',
                        }),
                    Infolists\Components\TextEntry::make('nombre')
                        ->label('Nombre del Producto')->columnSpan(2),
                    Infolists\Components\TextEntry::make('unidad_medida')
                        ->label('Unidad de Medida'),
                    Infolists\Components\TextEntry::make('descripcion')
                        ->label('Descripción')->columnSpanFull(),
                ]),

            Infolists\Components\Section::make('Clasificación y Precios')
                ->columns(4)
                ->schema([
                    Infolists\Components\TextEntry::make('categoria.nombre')
                        ->label('Categoría'),
                    Infolists\Components\TextEntry::make('proveedor.nombre')
                        ->label('Proveedor'),
                    Infolists\Components\TextEntry::make('precio_compra')
                        ->label('Precio Compra')->money('USD'),
                    Infolists\Components\TextEntry::make('precio_venta')
                        ->label('Precio Venta')->money('USD'),
                ]),

            Infolists\Components\Section::make('Stock por Sucursal')
                ->schema([
                    Infolists\Components\RepeatableEntry::make('inventarioAlmacen')
                        ->label('')
                        ->schema([
                            Infolists\Components\TextEntry::make('almacen.nombre')
                                ->label('Sucursal'),
                            Infolists\Components\TextEntry::make('stock_actual')
                                ->label('Stock Actual')
                                ->badge()
                                ->color(fn ($record) => match (true) {
                                    $record->stock_actual >= $record->stock_maximo => 'warning',
                                    $record->stock_actual <= $record->stock_minimo => 'danger',
                                    default                                        => 'success',
                                }),
                            Infolists\Components\TextEntry::make('stock_minimo')
                                ->label('Mínimo'),
                            Infolists\Components\TextEntry::make('stock_maximo')
                                ->label('Máximo'),
                            Infolists\Components\TextEntry::make('punto_reorden')
                                ->label('Punto Reorden'),
                        ])
                        ->columns(5),
                ]),

            Infolists\Components\Section::make('Información Adicional')
                ->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('ubicacion_almacen')
                        ->label('Ubicación en Almacén'),
                    Infolists\Components\TextEntry::make('peso_kg')
                        ->label('Peso (kg)'),
                    Infolists\Components\IconEntry::make('requiere_refrigeracion')
                        ->label('Requiere Refrigeración')->boolean(),
                    Infolists\Components\IconEntry::make('es_perecedero')
                        ->label('Es Perecedero')->boolean(),
                    Infolists\Components\TextEntry::make('vida_util_dias')
                        ->label('Vida Útil (días)')
                        ->visible(fn ($record) => $record->es_perecedero),
                ]),
        ]);
    }
}
