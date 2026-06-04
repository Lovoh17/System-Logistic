<?php

namespace App\Filament\Resources\AlmacenResource\Pages;

use App\Filament\Resources\AlmacenResource;
use App\Models\Almacen;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Collection;

class ListAlmacenes extends ListRecords
{
    protected static string $resource = AlmacenResource::class;
    protected static string $view     = 'filament.resources.almacen-resource.pages.list-almacenes';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nueva Sucursal')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getTodas(): Collection
    {
        return Almacen::orderBy('nombre')->get();
    }

    public function getConCoordenadas(): Collection
    {
        return Almacen::whereNotNull('latitud')
            ->whereNotNull('longitud')
            ->orderBy('nombre')
            ->get();
    }
}
