<?php

namespace App\Filament\Contador\Resources;

use App\Filament\Contador\Resources\ContabilidadResource\Pages;
use App\Models\AsientoContable;
use App\Models\CuentaContable;
use Filament\Resources\Resource;

class ContabilidadResource extends Resource
{
    protected static ?string $model            = AsientoContable::class;
    protected static ?string $navigationIcon   = 'heroicon-o-book-open';
    protected static ?string $navigationLabel  = 'Libro Diario';
    protected static ?string $navigationGroup  = 'Contabilidad';
    protected static ?int    $navigationSort   = 1;
    protected static ?string $modelLabel       = 'Asiento Contable';
    protected static ?string $pluralModelLabel = 'Libro Diario';

    public static function cuentasOptions(): array
    {
        return CuentaContable::movibles()
            ->orderBy('codigo')
            ->get()
            ->mapWithKeys(fn ($c) => [$c->id => "{$c->codigo} — {$c->nombre}"])
            ->toArray();
    }

    public static function getNavigationBadge(): ?string
    {
        $borradores = static::getModel()::where('estado', 'borrador')->count();
        return $borradores > 0 ? (string) $borradores : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListContabilidad::route('/'),
            'create' => Pages\CreateContabilidad::route('/create'),
            'edit'   => Pages\EditContabilidad::route('/{record}/edit'),
            'view'   => Pages\ViewContabilidad::route('/{record}'),
        ];
    }
}
