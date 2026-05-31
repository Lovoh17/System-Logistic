<?php

namespace App\Filament\Resources\MovimientoInventarioResource\Pages;

use App\Filament\Resources\MovimientoInventarioResource;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewMovimientoInventario extends ViewRecord
{
    protected static string $resource = MovimientoInventarioResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Movimiento')
                ->columns(4)
                ->schema([
                    Infolists\Components\TextEntry::make('numero')
                        ->badge()->color('gray'),
                    Infolists\Components\TextEntry::make('tipo')
                        ->label('Tipo')->badge(),
                    Infolists\Components\TextEntry::make('fecha_movimiento')
                        ->label('Fecha')->dateTime('d/m/Y H:i'),
                    Infolists\Components\TextEntry::make('user.name')
                        ->label('Registrado por'),
                ]),

            Infolists\Components\Section::make('Producto y Cantidades')
                ->columns(4)
                ->schema([
                    Infolists\Components\TextEntry::make('producto.nombre')
                        ->label('Producto')->columnSpan(2),
                    Infolists\Components\TextEntry::make('producto.codigo')
                        ->label('Código')->badge()->color('gray'),
                    Infolists\Components\TextEntry::make('cantidad')
                        ->label('Cantidad'),
                    Infolists\Components\TextEntry::make('costo_unitario')
                        ->label('Costo Unitario')->money('USD'),
                    Infolists\Components\TextEntry::make('costo_total')
                        ->label('Valor Total')->money('USD'),
                    Infolists\Components\TextEntry::make('stock_anterior')
                        ->label('Stock Anterior'),
                    Infolists\Components\TextEntry::make('stock_nuevo')
                        ->label('Stock Nuevo')->badge(),
                ]),

            Infolists\Components\Section::make('Detalles')
                ->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('lote')
                        ->label('Lote'),
                    Infolists\Components\TextEntry::make('fecha_vencimiento')
                        ->label('Vencimiento')->date('d/m/Y'),
                    Infolists\Components\TextEntry::make('motivo')
                        ->label('Motivo / Justificación')->columnSpanFull(),
                ]),
        ]);
    }
}
