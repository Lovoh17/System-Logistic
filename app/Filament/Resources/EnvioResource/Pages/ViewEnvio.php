<?php

namespace App\Filament\Resources\EnvioResource\Pages;

use App\Filament\Resources\EnvioResource;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewEnvio extends ViewRecord
{
    protected static string $resource = EnvioResource::class;

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
            Infolists\Components\Section::make('Estado del Envío')
                ->columns(4)
                ->schema([
                    Infolists\Components\TextEntry::make('numero')
                        ->badge()->color('primary'),
                    Infolists\Components\TextEntry::make('estado')
                        ->badge()->color(fn ($record) => $record->estado_color),
                    Infolists\Components\TextEntry::make('pedidoVenta.numero')
                        ->label('Pedido')->badge()->color('gray'),
                    Infolists\Components\TextEntry::make('transportista.nombre')
                        ->label('Transportista'),
                ]),

            Infolists\Components\Section::make('Ruta')
                ->columns(2)
                ->schema([
                    Infolists\Components\TextEntry::make('origen_nombre')
                        ->label('Origen'),
                    Infolists\Components\TextEntry::make('destino_nombre')
                        ->label('Destino'),
                    Infolists\Components\TextEntry::make('origen_direccion')
                        ->label('Dir. Origen'),
                    Infolists\Components\TextEntry::make('destino_direccion')
                        ->label('Dir. Destino'),
                    Infolists\Components\TextEntry::make('destino_departamento')
                        ->label('Departamento'),
                    Infolists\Components\TextEntry::make('destino_municipio')
                        ->label('Municipio'),
                ]),

            Infolists\Components\Section::make('Fechas y Carga')
                ->columns(4)
                ->schema([
                    Infolists\Components\TextEntry::make('fecha_programada')
                        ->label('Programado')->date('d/m/Y'),
                    Infolists\Components\TextEntry::make('fecha_entrega_estimada')
                        ->label('Est. Entrega')->dateTime('d/m/Y H:i'),
                    Infolists\Components\TextEntry::make('fecha_entrega_real')
                        ->label('Entrega Real')->dateTime('d/m/Y H:i'),
                    Infolists\Components\TextEntry::make('costo_envio')
                        ->label('Costo')->money('USD'),
                    Infolists\Components\TextEntry::make('peso_total_kg')
                        ->label('Peso (kg)'),
                    Infolists\Components\TextEntry::make('volumen_total_m3')
                        ->label('Volumen (m³)'),
                    Infolists\Components\TextEntry::make('distancia_km')
                        ->label('Distancia (km)'),
                    Infolists\Components\TextEntry::make('firma_receptor')
                        ->label('Recibido por'),
                ]),

            Infolists\Components\Section::make('Historial de Seguimiento')
                ->schema([
                    Infolists\Components\RepeatableEntry::make('seguimientos')
                        ->label('')
                        ->schema([
                            Infolists\Components\TextEntry::make('fecha_hora')
                                ->dateTime('d/m/Y H:i'),
                            Infolists\Components\TextEntry::make('evento')
                                ->weight('bold'),
                            Infolists\Components\TextEntry::make('ubicacion'),
                            Infolists\Components\TextEntry::make('descripcion'),
                        ])
                        ->columns(4),
                ]),
        ]);
    }
}
