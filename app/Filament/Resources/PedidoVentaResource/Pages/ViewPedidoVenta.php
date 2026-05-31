<?php

namespace App\Filament\Resources\PedidoVentaResource\Pages;

use App\Filament\Resources\PedidoVentaResource;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewPedidoVenta extends ViewRecord
{
    protected static string $resource = PedidoVentaResource::class;

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
            Infolists\Components\Section::make('Encabezado del Pedido')
                ->columns(4)
                ->schema([
                    Infolists\Components\TextEntry::make('numero')
                        ->badge()->color('primary'),
                    Infolists\Components\TextEntry::make('estado')
                        ->badge(),
                    Infolists\Components\TextEntry::make('prioridad')
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'urgente' => 'danger',
                            'alta'    => 'warning',
                            'normal'  => 'info',
                            default   => 'gray',
                        }),
                    Infolists\Components\TextEntry::make('canal_venta')
                        ->label('Canal')->badge()->color('gray'),
                    Infolists\Components\TextEntry::make('cliente.nombre')
                        ->label('Cliente')->columnSpan(2),
                    Infolists\Components\TextEntry::make('almacen.nombre')
                        ->label('Sucursal'),
                    Infolists\Components\TextEntry::make('user.name')
                        ->label('Creado por'),
                    Infolists\Components\TextEntry::make('fecha_pedido')
                        ->label('Fecha Pedido')->date('d/m/Y'),
                    Infolists\Components\TextEntry::make('fecha_requerida')
                        ->label('Fecha Requerida')->date('d/m/Y'),
                ]),

            Infolists\Components\Section::make('Dirección de Entrega')
                ->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('departamento_entrega')
                        ->label('Departamento'),
                    Infolists\Components\TextEntry::make('municipio_entrega')
                        ->label('Municipio'),
                    Infolists\Components\TextEntry::make('direccion_entrega')
                        ->label('Dirección'),
                    Infolists\Components\TextEntry::make('instrucciones_entrega')
                        ->label('Instrucciones')->columnSpanFull(),
                ]),

            Infolists\Components\Section::make('Líneas de Pedido')
                ->schema([
                    Infolists\Components\RepeatableEntry::make('items')
                        ->label('')
                        ->schema([
                            Infolists\Components\TextEntry::make('producto.nombre')
                                ->label('Producto'),
                            Infolists\Components\TextEntry::make('cantidad'),
                            Infolists\Components\TextEntry::make('precio_unitario')
                                ->label('Precio Unit.')->money('USD'),
                            Infolists\Components\TextEntry::make('descuento')
                                ->label('Desc. %')->suffix('%'),
                            Infolists\Components\TextEntry::make('subtotal')
                                ->money('USD'),
                        ])
                        ->columns(5),
                ]),

            Infolists\Components\Section::make('Totales')
                ->columns(4)
                ->schema([
                    Infolists\Components\TextEntry::make('subtotal')
                        ->label('Subtotal')->money('USD'),
                    Infolists\Components\TextEntry::make('impuesto')
                        ->label('IVA (13%)')->money('USD'),
                    Infolists\Components\TextEntry::make('costo_envio')
                        ->label('Costo Envío')->money('USD'),
                    Infolists\Components\TextEntry::make('total')
                        ->label('TOTAL')->money('USD')->weight('bold'),
                    Infolists\Components\TextEntry::make('notas')
                        ->label('Observaciones')->columnSpanFull(),
                ]),
        ]);
    }
}
