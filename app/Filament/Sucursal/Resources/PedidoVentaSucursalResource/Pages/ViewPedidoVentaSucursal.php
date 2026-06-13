<?php

namespace App\Filament\Sucursal\Resources\PedidoVentaSucursalResource\Pages;

use App\Filament\Sucursal\Resources\PedidoVentaSucursalResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewPedidoVentaSucursal extends ViewRecord
{
    protected static string $resource = PedidoVentaSucursalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('confirmar')
                ->label('Confirmar')
                ->icon('heroicon-m-check')
                ->color('info')
                ->visible(fn () => $this->record->estado === 'borrador')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update(['estado' => 'confirmado']);
                    Notification::make()->success()->title('Pedido confirmado')->send();
                    $this->record->refresh();
                }),

            Actions\Action::make('marcar_listo')
                ->label('Listo para Despacho')
                ->icon('heroicon-m-check-circle')
                ->color('primary')
                ->visible(fn () => $this->record->estado === 'en_preparacion')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update(['estado' => 'listo']);
                    Notification::make()->success()->title('Pedido listo para despacho')->send();
                    $this->record->refresh();
                }),

            Actions\EditAction::make()
                ->visible(fn () => in_array($this->record->estado, ['borrador', 'confirmado'])),
        ];
    }
}
