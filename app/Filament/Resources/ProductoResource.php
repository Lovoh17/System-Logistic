<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductoResource\Pages;
use App\Models\Producto;
use Filament\Resources\Resource;

class ProductoResource extends Resource
{
    protected static ?string $model = Producto::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel = 'Productos';
    protected static ?string $navigationGroup = 'Inventario';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'Producto';
    protected static ?string $pluralModelLabel = 'Productos';

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProducto::route('/'),
            'create' => Pages\CreateProducto::route('/create'),
            'view'   => Pages\ViewProducto::route('/{record}'),
            'edit'   => Pages\EditProducto::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $sinStock = static::getModel()::whereDoesntHave('inventarioAlmacen', function ($q) {
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
}
