<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PedidoVentaResource\Pages;
use App\Models\PedidoVenta;
use Filament\Resources\Resource;

class PedidoVentaResource extends Resource
{
    protected static ?string $model = PedidoVenta::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Pedidos de Venta';
    protected static ?string $navigationGroup = 'Pedidos';
    protected static ?int $navigationSort = 2;
    protected static ?string $modelLabel = 'Pedido de Venta';
    protected static ?string $pluralModelLabel = 'Pedidos de Venta';

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPedidoVenta::route('/'),
            'create' => Pages\CreatePedidoVenta::route('/create'),
            'view'   => Pages\ViewPedidoVenta::route('/{record}'),
            'edit'   => Pages\EditPedidoVenta::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $urgentes = static::getModel()::whereIn('estado', ['confirmado', 'en_preparacion'])->count();
        return $urgentes > 0 ? (string) $urgentes : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
