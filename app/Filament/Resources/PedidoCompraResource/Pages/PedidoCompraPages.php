<?php
namespace App\Filament\Resources\PedidoCompraResource\Pages;

use App\Filament\Resources\PedidoCompraResource;
use Filament\Actions;
use Filament\Resources\Pages\{ListRecords, CreateRecord, EditRecord, ViewRecord};

class ListPedidosCompra extends ListRecords
{
    protected static string $resource = PedidoCompraResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
class CreatePedidoCompra extends CreateRecord
{
    protected static string $resource = PedidoCompraResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
class EditPedidoCompra extends EditRecord
{
    protected static string $resource = PedidoCompraResource::class;
    protected function getHeaderActions(): array { return [Actions\ViewAction::make(), Actions\DeleteAction::make()]; }
}
class ViewPedidoCompra extends ViewRecord
{
    protected static string $resource = PedidoCompraResource::class;
    protected function getHeaderActions(): array { return [Actions\EditAction::make()]; }
}
