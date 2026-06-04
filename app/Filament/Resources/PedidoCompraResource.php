<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PedidoCompraResource\Pages;
use App\Models\PedidoCompra;
use Filament\Resources\Resource;

class PedidoCompraResource extends Resource
{
    protected static ?string $model = PedidoCompra::class;

    protected static ?string $navigationIcon  = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel = 'Órdenes de Compra';
    protected static ?string $navigationGroup = 'Pedidos';
    protected static ?int    $navigationSort  = 1;
    protected static ?string $modelLabel      = 'Orden de Compra';
    protected static ?string $pluralModelLabel = 'Órdenes de Compra';

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPedidoCompra::route('/'),
            'create' => Pages\CreatePedidoCompra::route('/create'),
            'view'   => Pages\ViewPedidoCompra::route('/{record}'),
            'edit'   => Pages\EditPedidoCompra::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $pendientes = static::getModel()::whereIn('estado', ['enviado', 'confirmado'])->count();
        return $pendientes > 0 ? (string) $pendientes : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
