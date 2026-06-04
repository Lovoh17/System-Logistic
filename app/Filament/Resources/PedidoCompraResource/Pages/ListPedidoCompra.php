<?php

namespace App\Filament\Resources\PedidoCompraResource\Pages;

use App\Filament\Pages\RecomendacionesCompra;
use App\Filament\Resources\PedidoCompraResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPedidoCompra extends ListRecords
{
    protected static string $resource = PedidoCompraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            Actions\Action::make('recomendaciones_compra')
                ->label('Recomendaciones de Compra')
                ->icon('heroicon-o-light-bulb')
                ->color('warning')
                ->url(fn() => RecomendacionesCompra::getUrl()),
        ];
    }
}
