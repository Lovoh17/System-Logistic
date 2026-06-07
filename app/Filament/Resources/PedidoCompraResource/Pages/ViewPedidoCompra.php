<?php

namespace App\Filament\Resources\PedidoCompraResource\Pages;

use App\Filament\Resources\PedidoCompraResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPedidoCompra extends ViewRecord
{
    protected static string $resource = PedidoCompraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('recibir')
                ->label('Registrar Recepción')
                ->icon('heroicon-o-inbox-arrow-down')
                ->color('success')
                ->visible(fn() => in_array($this->record->estado, ['enviado', 'confirmado', 'parcial']))
                ->url(fn() => PedidoCompraResource::getUrl('recibir', ['record' => $this->record])),

            Actions\EditAction::make(),
        ];
    }
}
