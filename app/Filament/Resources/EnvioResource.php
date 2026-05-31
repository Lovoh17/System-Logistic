<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnvioResource\Pages;
use App\Models\Envio;
use Filament\Resources\Resource;

class EnvioResource extends Resource
{
    protected static ?string $model = Envio::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationLabel = 'Envíos y Transporte';
    protected static ?string $navigationGroup = 'Logística';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'Envío';
    protected static ?string $pluralModelLabel = 'Envíos';

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEnvio::route('/'),
            'create' => Pages\CreateEnvio::route('/create'),
            'view'   => Pages\ViewEnvio::route('/{record}'),
            'edit'   => Pages\EditEnvio::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $enRuta = static::getModel()::whereIn('estado', ['despachado', 'en_transito', 'en_destino'])->count();
        return $enRuta > 0 ? (string) $enRuta : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }
}
