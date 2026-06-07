<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TrasladoResource\Pages;
use App\Models\Traslado;
use Filament\Resources\Resource;

class TrasladoResource extends Resource
{
    protected static ?string $model = Traslado::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';
    protected static ?string $navigationLabel = 'Traslados';
    protected static ?string $navigationGroup = 'Logística';
    protected static ?int $navigationSort = 2;
    protected static ?string $modelLabel = 'Traslado';
    protected static ?string $pluralModelLabel = 'Traslados';

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTraslados::route('/'),
            'create' => Pages\CreateTraslado::route('/create'),
            'view'   => Pages\ViewTraslado::route('/{record}'),
            'edit'   => Pages\EditTraslado::route('/{record}/edit'),
        ];
    }
}
