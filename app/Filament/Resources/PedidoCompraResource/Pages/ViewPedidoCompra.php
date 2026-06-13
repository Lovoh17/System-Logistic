<?php

namespace App\Filament\Resources\PedidoCompraResource\Pages;

use App\Filament\Resources\PedidoCompraResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewPedidoCompra extends ViewRecord
{
    protected static string $resource = PedidoCompraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('enviar_proveedor')
                ->label('Enviar al Proveedor')
                ->icon('heroicon-o-paper-airplane')
                ->color('info')
                ->visible(fn () => $this->record->estado === 'borrador')
                ->requiresConfirmation()
                ->modalHeading('¿Enviar Orden de Compra al Proveedor?')
                ->modalDescription('Se marcará como enviada.')
                ->action(function () {
                    if ($this->record->proveedor && $this->record->proveedor->estado !== 'activo') {
                        Notification::make()->danger()
                            ->title('No se puede enviar')
                            ->body("El proveedor \"{$this->record->proveedor->nombre}\" no está activo.")
                            ->send();

                        return;
                    }
                    $this->record->update(['estado' => 'enviado']);
                    Notification::make()->success()->title('OC enviada al proveedor')->send();
                    $this->record->refresh();
                }),

            Actions\Action::make('confirmar_oc')
                ->label('Confirmar OC')
                ->icon('heroicon-o-check-badge')
                ->color('primary')
                ->visible(fn () => in_array($this->record->estado, ['borrador', 'enviado']))
                ->requiresConfirmation()
                ->modalHeading('Confirmar Orden de Compra')
                ->modalDescription('¿El proveedor confirmó la orden? Se marcará como Confirmada y quedará lista para recepción.')
                ->action(function () {
                    $this->record->update(['estado' => 'confirmado']);
                    Notification::make()->success()->title('OC confirmada por el proveedor')->send();
                    $this->record->refresh();
                }),

            Actions\Action::make('recibir')
                ->label('Registrar Recepción')
                ->icon('heroicon-o-inbox-arrow-down')
                ->color('success')
                ->visible(fn () => in_array($this->record->estado, ['enviado', 'confirmado', 'parcial']))
                ->url(fn () => PedidoCompraResource::getUrl('recibir', ['record' => $this->record])),

            Actions\Action::make('cancelar')
                ->label('Cancelar OC')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => ! in_array($this->record->estado, ['recibido', 'cancelado']))
                ->modalHeading('Cancelar Orden de Compra')
                ->form([
                    Forms\Components\Textarea::make('motivo_cancelacion')
                        ->label('Motivo de Cancelación')
                        ->required()
                        ->minLength(10)
                        ->rows(3)
                        ->placeholder('Indique el motivo de cancelación (mínimo 10 caracteres)'),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'estado' => 'cancelado',
                        'motivo_cancelacion' => $data['motivo_cancelacion'],
                    ]);
                    Notification::make()->success()->title('Orden de compra cancelada')->send();
                    $this->record->refresh();
                }),

            Actions\EditAction::make(),
        ];
    }
}
