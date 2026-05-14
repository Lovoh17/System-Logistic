<?php
namespace App\Filament\Resources\PedidoVentaResource\Pages;

use App\Filament\Resources\PedidoVentaResource;
use Filament\Actions;
use Filament\Resources\Pages\{ListRecords, CreateRecord, EditRecord, ViewRecord};

class ListPedidosVenta extends ListRecords
{
    protected static string $resource = PedidoVentaResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
class CreatePedidoVenta extends CreateRecord
{
    protected static string $resource = PedidoVentaResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        return $data;
    }
}
class EditPedidoVenta extends EditRecord
{
    protected static string $resource = PedidoVentaResource::class;
    protected function getHeaderActions(): array { return [Actions\ViewAction::make(), Actions\DeleteAction::make()]; }
}
class ViewPedidoVenta extends ViewRecord
{
    protected static string $resource = PedidoVentaResource::class;
    protected function getHeaderActions(): array { return [Actions\EditAction::make()]; }
}


// ════════════════════════════════════════════════════
// PAGES — ProductoResource
// ════════════════════════════════════════════════════
namespace App\Filament\Resources\ProductoResource\Pages;

use App\Filament\Resources\ProductoResource;
use Filament\Actions;
use Filament\Resources\Pages\{ListRecords, CreateRecord, EditRecord};

class ListProductos extends ListRecords
{
    protected static string $resource = ProductoResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
class CreateProducto extends CreateRecord
{
    protected static string $resource = ProductoResource::class;
}
class EditProducto extends EditRecord
{
    protected static string $resource = ProductoResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
}


// ════════════════════════════════════════════════════
// PAGES — EnvioResource
// ════════════════════════════════════════════════════
namespace App\Filament\Resources\EnvioResource\Pages;

use App\Filament\Resources\EnvioResource;
use Filament\Actions;
use Filament\Resources\Pages\{ListRecords, CreateRecord, EditRecord, ViewRecord};

class ListEnvios extends ListRecords
{
    protected static string $resource = EnvioResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
class CreateEnvio extends CreateRecord
{
    protected static string $resource = EnvioResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        return $data;
    }
}
class EditEnvio extends EditRecord
{
    protected static string $resource = EnvioResource::class;
    protected function getHeaderActions(): array { return [Actions\ViewAction::make(), Actions\DeleteAction::make()]; }
}
class ViewEnvio extends ViewRecord
{
    protected static string $resource = EnvioResource::class;
    protected function getHeaderActions(): array { return [Actions\EditAction::make()]; }
}
