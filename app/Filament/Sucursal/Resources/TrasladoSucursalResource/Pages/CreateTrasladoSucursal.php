<?php

namespace App\Filament\Sucursal\Resources\TrasladoSucursalResource\Pages;

use App\Filament\Sucursal\Resources\TrasladoSucursalResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTrasladoSucursal extends CreateRecord
{
    protected static string $resource = TrasladoSucursalResource::class;

    /**
     * Refuerza en servidor que el origen sea la sucursal del usuario y
     * registra al creador (columna obligatoria).
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $almacenId = auth()->user()?->almacen_id;

        $data['almacen_origen_id'] = $almacenId;
        $data['creado_por'] = auth()->id();

        return $data;
    }
}
