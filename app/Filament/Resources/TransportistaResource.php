<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransportistaResource\Pages;
use App\Models\Transportista;
use Filament\Resources\Resource;

class TransportistaResource extends Resource
{
    protected static ?string $model = Transportista::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationLabel = 'Transportistas';
    protected static ?string $navigationGroup = 'Logística';
    protected static ?int $navigationSort = 2;
    protected static ?string $modelLabel = 'Transportista';
    protected static ?string $pluralModelLabel = 'Transportistas';

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTransportistas::route('/'),
            'create' => Pages\CreateTransportista::route('/create'),
            'view'   => Pages\ViewTransportista::route('/{record}'),
            'edit'   => Pages\EditTransportista::route('/{record}/edit'),
        ];
    }
}
