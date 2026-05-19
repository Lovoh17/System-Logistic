<?php
namespace App\Filament\Resources\ClienteResource\Pages;

use App\Filament\Resources\ClienteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ViewRecord;

class ListClientes extends ListRecords
{
    protected static string $resource = ClienteResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
class CreateCliente extends CreateRecord
{
    protected static string $resource = ClienteResource::class;
}
class EditCliente extends EditRecord
{
    protected static string $resource = ClienteResource::class;
    protected function getHeaderActions(): array { return [Actions\ViewAction::make(), Actions\DeleteAction::make()]; }
}
class ViewCliente extends ViewRecord
{
    protected static string $resource = ClienteResource::class;
    protected function getHeaderActions(): array { return [Actions\EditAction::make()]; }
}
