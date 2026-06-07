<?php

namespace App\Filament\Resources\TransportistaResource\Pages;

use App\Filament\Resources\TransportistaResource;
use App\Models\Transportista;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;

class CreateTransportista extends CreateRecord
{
    protected static string $resource = TransportistaResource::class;

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Información del Transportista')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('codigo')
                        ->label('Código')
                        ->default(fn () => Transportista::generarCodigo())
                        ->disabled()->dehydrated()->required()->columnSpan(1),
                    Forms\Components\TextInput::make('nombre')
                        ->label('Nombre / Empresa')->required()->maxLength(150)->columnSpan(2),
                    Forms\Components\Select::make('tipo')
                        ->options(['propio' => 'Flota Propia', 'externo' => 'Externo'])
                        ->default('externo')->required()->columnSpan(1),
                    Forms\Components\Select::make('estado')
                        ->options([
                            'disponible'    => 'Disponible',
                            'en_ruta'       => 'En Ruta',
                            'mantenimiento' => 'Mantenimiento',
                            'inactivo'      => 'Inactivo',
                        ])
                        ->default('disponible')->required()->columnSpan(1),
                    Forms\Components\TextInput::make('email')->email()->columnSpan(1),
                    Forms\Components\TextInput::make('telefono')->label('Teléfono')->columnSpan(1),
                ]),

            Forms\Components\Section::make('Vehículo')
                ->columns(3)
                ->schema([
                    Forms\Components\Select::make('vehiculo_tipo')
                        ->label('Tipo de Vehículo')
                        ->options([
                            'camion' => 'Camión', 'pickup' => 'Pickup', 'furgon' => 'Furgón',
                            'moto'   => 'Motocicleta', 'otro' => 'Otro',
                        ])->columnSpan(1),
                    Forms\Components\TextInput::make('vehiculo_placa')->label('Placa')->maxLength(20)->columnSpan(1),
                    Forms\Components\TextInput::make('vehiculo_modelo')->label('Modelo')->maxLength(80)->columnSpan(1),
                    Forms\Components\TextInput::make('capacidad_kg')->label('Capacidad Peso (kg)')->numeric()->columnSpan(1),
                    Forms\Components\TextInput::make('capacidad_m3')->label('Capacidad Volumen (m³)')->numeric()->columnSpan(1),
                    Forms\Components\Toggle::make('tiene_refrigeracion')->label('¿Tiene Refrigeración?')->columnSpan(1),
                    Forms\Components\Toggle::make('tiene_gps')->label('¿Tiene GPS?')->columnSpan(1),
                ]),

            Forms\Components\Section::make('Conductor')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('conductor_nombre')->label('Nombre del Conductor')->maxLength(100)->columnSpan(1),
                    Forms\Components\TextInput::make('conductor_licencia')->label('N° Licencia')->maxLength(30)->columnSpan(1),
                    Forms\Components\TextInput::make('conductor_telefono')->label('Teléfono del Conductor')->maxLength(20)->columnSpan(1),
                ]),

            Forms\Components\Section::make('Tarifas')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('tarifa_km')->label('Tarifa por Km ($)')->numeric()->prefix('$')->step(0.01)->columnSpan(1),
                    Forms\Components\TextInput::make('tarifa_fija')->label('Tarifa Fija ($)')->numeric()->prefix('$')->step(0.01)->columnSpan(1),
                    Forms\Components\Textarea::make('notas')->label('Notas')->rows(2)->columnSpanFull(),
                ]),
        ]);
    }
}
