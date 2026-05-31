<?php

namespace App\Filament\Resources\PedidoCompraResource\Pages;

use App\Filament\Resources\PedidoCompraResource;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewPedidoCompra extends ViewRecord
{
    protected static string $resource = PedidoCompraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Orden de Compra')
                ->columns(4)
                ->schema([
                    Infolists\Components\TextEntry::make('numero')
                        ->badge()->color('primary'),
                    Infolists\Components\TextEntry::make('estado')
                        ->badge()->color(fn ($record) => $record->estado_color),
                    Infolists\Components\TextEntry::make('proveedor.nombre')
                        ->label('Proveedor'),
                    Infolists\Components\TextEntry::make('total')
                        ->money('USD')->weight('bold'),
                    Infolists\Components\TextEntry::make('fecha_pedido')
                        ->label('Fecha Pedido')->date('d/m/Y'),
                    Infolists\Components\TextEntry::make('fecha_requerida')
                        ->label('Requerida')->date('d/m/Y'),
                    Infolists\Components\TextEntry::make('fecha_recepcion')
                        ->label('Recibida')->date('d/m/Y'),
                    Infolists\Components\TextEntry::make('user.name')
                        ->label('Creado por'),
                ]),

            Infolists\Components\Section::make('Productos')
                ->schema([
                    Infolists\Components\RepeatableEntry::make('items')
                        ->label('')
                        ->schema([
                            Infolists\Components\TextEntry::make('producto.nombre')
                                ->label('Producto'),
                            Infolists\Components\TextEntry::make('cantidad'),
                            Infolists\Components\TextEntry::make('cantidad_recibida')
                                ->label('Recibida'),
                            Infolists\Components\TextEntry::make('precio_unitario')
                                ->label('P. Unit.')->money('USD'),
                            Infolists\Components\TextEntry::make('subtotal')
                                ->money('USD'),
                        ])
                        ->columns(5),
                ]),

            Infolists\Components\Section::make('Condiciones')
                ->columns(2)
                ->schema([
                    Infolists\Components\TextEntry::make('condiciones_pago')
                        ->label('Condiciones de Pago'),
                    Infolists\Components\TextEntry::make('notas')
                        ->label('Notas / Instrucciones'),
                ]),
        ]);
    }
}
