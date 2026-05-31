<?php

namespace App\Filament\Resources\TrasladoResource\Pages;

use App\Filament\Resources\TrasladoResource;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewTraslado extends ViewRecord
{
    protected static string $resource = TrasladoResource::class;

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
            Infolists\Components\Section::make('Traslado')
                ->columns(4)
                ->schema([
                    Infolists\Components\TextEntry::make('numero')
                        ->badge()->color('primary'),
                    Infolists\Components\TextEntry::make('estado')
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'pendiente'   => 'warning',
                            'asignado'    => 'info',
                            'en_transito' => 'primary',
                            'entregado'   => 'success',
                            'cancelado'   => 'danger',
                            default       => 'gray',
                        }),
                    Infolists\Components\TextEntry::make('producto.nombre')
                        ->label('Producto')->columnSpan(2),
                    Infolists\Components\TextEntry::make('almacenOrigen.nombre')
                        ->label('Sucursal Origen')->badge()->color('gray'),
                    Infolists\Components\TextEntry::make('almacenDestino.nombre')
                        ->label('Sucursal Destino')->badge()->color('gray'),
                    Infolists\Components\TextEntry::make('cantidad')
                        ->label('Cantidad'),
                    Infolists\Components\TextEntry::make('cantidad_recibida')
                        ->label('Cantidad Recibida'),
                ]),

            Infolists\Components\Section::make('Transporte y Fechas')
                ->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('transportista.nombre')
                        ->label('Transportista'),
                    Infolists\Components\TextEntry::make('fecha_programada')
                        ->label('Fecha Programada')->date('d/m/Y'),
                    Infolists\Components\TextEntry::make('fecha_entrega_estimada')
                        ->label('Estimada')->date('d/m/Y'),
                    Infolists\Components\TextEntry::make('fecha_salida')
                        ->label('Salida')->date('d/m/Y'),
                    Infolists\Components\TextEntry::make('fecha_entrega_real')
                        ->label('Entrega Real')->date('d/m/Y'),
                ]),

            Infolists\Components\Section::make('Información Adicional')
                ->columns(2)
                ->schema([
                    Infolists\Components\TextEntry::make('motivo')
                        ->label('Motivo'),
                    Infolists\Components\TextEntry::make('observaciones')
                        ->label('Observaciones'),
                ]),
        ]);
    }
}
