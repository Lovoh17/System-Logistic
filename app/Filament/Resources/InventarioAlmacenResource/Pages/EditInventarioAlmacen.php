<?php

namespace App\Filament\Resources\InventarioAlmacenResource\Pages;

use App\Filament\Resources\InventarioAlmacenResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInventarioAlmacen extends EditRecord
{
    protected static string $resource = InventarioAlmacenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
