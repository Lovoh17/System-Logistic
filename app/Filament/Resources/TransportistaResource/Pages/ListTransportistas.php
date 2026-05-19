<?php
namespace App\Filament\Resources\TransportistaResource\Pages;

use App\Filament\Resources\TransportistaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;

class ListTransportistas extends ListRecords
{
    protected static string $resource = TransportistaResource::class;
    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}

class CreateTransportista extends CreateRecord
{
    protected static string $resource = TransportistaResource::class;
}


