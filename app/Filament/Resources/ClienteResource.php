<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClienteResource\Pages;
use App\Models\Cliente;
use Filament\Resources\Resource;

class ClienteResource extends Resource
{
    protected static ?string $model = Cliente::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Clientes';
    protected static ?string $navigationGroup = 'Gestión de Socios';
    protected static ?int $navigationSort = 2;
    protected static ?string $modelLabel = 'Cliente';
    protected static ?string $pluralModelLabel = 'Clientes';

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCliente::route('/'),
            'create' => Pages\CreateCliente::route('/create'),
            'view'   => Pages\ViewCliente::route('/{record}'),
            'edit'   => Pages\EditCliente::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) Cliente::where('estado', 'activo')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }
}
