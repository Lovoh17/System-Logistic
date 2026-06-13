<?php

namespace App\Filament\Resources\PedidoVentaResource\Pages;

use App\Filament\Resources\PedidoVentaResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPedidoVenta extends ViewRecord
{
    protected static string $resource = PedidoVentaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generar_envio')
                ->label('Crear Envío')
                ->icon('heroicon-o-truck')
                ->color('success')
                ->visible(fn () => in_array($this->record->estado, ['listo', 'confirmado']))
                ->url(fn () => route('filament.admin.resources.envios.create', ['pedido_venta_id' => $this->record->id])),

            Actions\EditAction::make(),
        ];
    }
}
