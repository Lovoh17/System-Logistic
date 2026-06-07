<?php

namespace App\Filament\Resources\TrasladoResource\Pages;

use App\Filament\Resources\TrasladoResource;
use App\Models\User;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateTraslado extends CreateRecord
{
    protected static string $resource = TrasladoResource::class;

    protected function afterCreate(): void
    {
        $destinoAdmins = User::role('admin_sucursal')
            ->where('almacen_id', $this->record->almacen_destino_id)
            ->get();

        if ($destinoAdmins->isNotEmpty()) {
            Notification::make()
                ->title('Redistribución automática por orden de compra')
                ->info()
                ->sendToDatabase($destinoAdmins);
        }
    }
}
