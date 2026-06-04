<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventarioAlmacenResource\Pages;
use App\Models\InventarioAlmacen;
use Filament\Resources\Resource;

class InventarioAlmacenResource extends Resource
{
    protected static ?string $model = InventarioAlmacen::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel = 'Inventario por Sucursal';
    protected static ?string $navigationGroup = 'Inventario';
    protected static ?int $navigationSort = 2;
    protected static ?string $modelLabel = 'Inventario';
    protected static ?string $pluralModelLabel = 'Inventario por Sucursal';

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventarioAlmacens::route('/'),
        ];
    }
}
