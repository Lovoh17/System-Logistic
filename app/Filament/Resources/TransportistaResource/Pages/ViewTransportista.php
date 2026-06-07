<?php

namespace App\Filament\Resources\TransportistaResource\Pages;

use App\Filament\Resources\TransportistaResource;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewTransportista extends ViewRecord
{
    protected static string $resource = TransportistaResource::class;

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
            Infolists\Components\Section::make('Información del Transportista')
                ->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('codigo')->badge()->color('gray'),
                    Infolists\Components\TextEntry::make('nombre')->label('Nombre / Empresa'),
                    Infolists\Components\TextEntry::make('tipo')->badge()->color('info'),
                    Infolists\Components\TextEntry::make('estado')
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'disponible'    => 'success',
                            'en_ruta'       => 'warning',
                            'mantenimiento' => 'danger',
                            default         => 'gray',
                        }),
                    Infolists\Components\TextEntry::make('email'),
                    Infolists\Components\TextEntry::make('telefono'),
                ]),

            Infolists\Components\Section::make('Vehículo')
                ->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('vehiculo_tipo')->label('Tipo'),
                    Infolists\Components\TextEntry::make('vehiculo_placa')->label('Placa'),
                    Infolists\Components\TextEntry::make('vehiculo_modelo')->label('Modelo'),
                    Infolists\Components\TextEntry::make('capacidad_kg')->label('Capacidad (kg)'),
                    Infolists\Components\TextEntry::make('capacidad_m3')->label('Capacidad (m³)'),
                    Infolists\Components\IconEntry::make('tiene_refrigeracion')
                        ->label('Refrigeración')->boolean(),
                    Infolists\Components\IconEntry::make('tiene_gps')
                        ->label('GPS')->boolean(),
                ]),

            Infolists\Components\Section::make('Conductor')
                ->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('conductor_nombre')->label('Nombre'),
                    Infolists\Components\TextEntry::make('conductor_licencia')->label('N° Licencia'),
                    Infolists\Components\TextEntry::make('conductor_telefono')->label('Teléfono'),
                ]),

            Infolists\Components\Section::make('Tarifas')
                ->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('tarifa_km')->label('Tarifa por Km')->money('USD'),
                    Infolists\Components\TextEntry::make('tarifa_fija')->label('Tarifa Fija')->money('USD'),
                    Infolists\Components\TextEntry::make('notas')->label('Notas')->columnSpanFull(),
                ]),
        ]);
    }
}
