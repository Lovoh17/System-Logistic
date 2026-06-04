<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MovimientoInventarioResource\Pages;
use App\Models\MovimientoInventario;
use Filament\Resources\Resource;

class MovimientoInventarioResource extends Resource
{
    protected static ?string $model = MovimientoInventario::class;

    protected static ?string $navigationIcon   = 'heroicon-o-arrows-right-left';
    protected static ?string $navigationLabel  = 'Kardex / Movimientos';
    protected static ?string $navigationGroup  = 'Inventario';
    protected static ?int    $navigationSort   = 2;
    protected static ?string $modelLabel       = 'Movimiento';
    protected static ?string $pluralModelLabel = 'Movimientos de Inventario';

    public static function canCreate(): bool        { return true; }
    public static function canEdit($record): bool   { return false; }
    public static function canDelete($record): bool { return false; }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListMovimientoInventario::route('/'),
            'create' => Pages\CreateMovimientoInventario::route('/create'),
            'view'   => Pages\ViewMovimientoInventario::route('/{record}'),
        ];
    }
}
