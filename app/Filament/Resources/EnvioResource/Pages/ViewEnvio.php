<?php

namespace App\Filament\Resources\EnvioResource\Pages;

use App\Filament\Resources\EnvioResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ViewRecord;

class ViewEnvio extends ViewRecord
{
    protected static string $resource = EnvioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('registrar_seguimiento')
                ->label('Registrar Evento')
                ->icon('heroicon-o-map-pin')
                ->color('info')
                ->form([
                    Forms\Components\TextInput::make('evento')
                        ->label('Evento')
                        ->required()
                        ->placeholder('Ej: Salida de bodega, Llegada a destino'),
                    Forms\Components\TextInput::make('ubicacion')
                        ->label('Ubicación'),
                    Forms\Components\DateTimePicker::make('fecha_hora')
                        ->label('Fecha y Hora')
                        ->default(now())
                        ->required(),
                    Forms\Components\Textarea::make('descripcion')
                        ->label('Descripción')->rows(2),
                ])
                ->action(function (array $data) {
                    $this->record->seguimientos()->create([
                        ...$data,
                        'responsable' => auth()->user()->name,
                    ]);
                }),

            Actions\EditAction::make(),
        ];
    }
}
