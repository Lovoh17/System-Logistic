<?php

namespace App\Filament\Resources\EnvioResource\Pages;

use App\Filament\Resources\EnvioResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;

class EditEnvio extends EditRecord
{
    protected static string $resource = EnvioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Datos del Envío')
                ->icon('heroicon-o-truck')
                ->columns(4)
                ->schema([
                    Forms\Components\TextInput::make('numero')
                        ->label('N° Envío')
                        ->disabled()->dehydrated()->required()
                        ->columnSpan(1),

                    Forms\Components\Select::make('pedido_venta_id')
                        ->label('Pedido de Venta')
                        ->relationship('pedidoVenta', 'numero')
                        ->searchable()->preload()->required()
                        ->columnSpan(2),

                    Forms\Components\Select::make('estado')
                        ->options([
                            'programado'     => 'Programado',
                            'en_preparacion' => 'En Preparación',
                            'despachado'     => 'Despachado',
                            'en_transito'    => 'En Tránsito',
                            'en_destino'     => 'En Destino',
                            'entregado'      => 'Entregado',
                            'fallido'        => 'Fallido',
                            'devuelto'       => 'Devuelto',
                        ])
                        ->required()->columnSpan(1),

                    Forms\Components\Select::make('transportista_id')
                        ->label('Transportista / Unidad')
                        ->relationship('transportista', 'nombre')
                        ->searchable()->preload()->columnSpan(2),

                    Forms\Components\DatePicker::make('fecha_programada')
                        ->label('Fecha Programada')->required()->columnSpan(1),

                    Forms\Components\DateTimePicker::make('fecha_salida')
                        ->label('Fecha/Hora de Salida')->columnSpan(1),
                ]),

            Forms\Components\Section::make('Origen y Destino')
                ->icon('heroicon-o-map')
                ->columns(2)
                ->schema([
                    Forms\Components\Group::make([
                        Forms\Components\TextInput::make('origen_nombre')->label('Lugar de Origen')->required(),
                        Forms\Components\Textarea::make('origen_direccion')->label('Dirección de Origen')->rows(2),
                    ])->columnSpan(1),

                    Forms\Components\Group::make([
                        Forms\Components\TextInput::make('destino_nombre')->label('Destino / Receptor')->required(),
                        Forms\Components\TextInput::make('destino_departamento')->label('Departamento'),
                        Forms\Components\TextInput::make('destino_municipio')->label('Municipio'),
                        Forms\Components\Textarea::make('destino_direccion')->label('Dirección de Destino')->rows(2),
                    ])->columnSpan(1),
                ]),

            Forms\Components\Section::make('Detalles de Carga')
                ->icon('heroicon-o-scale')
                ->columns(4)
                ->schema([
                    Forms\Components\TextInput::make('peso_total_kg')
                        ->label('Peso Total (kg)')->numeric()->step(0.001)->columnSpan(1),
                    Forms\Components\TextInput::make('volumen_total_m3')
                        ->label('Volumen (m³)')->numeric()->step(0.001)->columnSpan(1),
                    Forms\Components\TextInput::make('distancia_km')
                        ->label('Distancia (km)')->numeric()->step(0.01)->columnSpan(1),
                    Forms\Components\TextInput::make('costo_envio')
                        ->label('Costo de Envío ($)')->numeric()->prefix('$')->columnSpan(1),
                ]),

            Forms\Components\Section::make('Entrega')
                ->icon('heroicon-o-check-circle')
                ->columns(3)
                ->schema([
                    Forms\Components\DateTimePicker::make('fecha_entrega_estimada')
                        ->label('Entrega Estimada')->columnSpan(1),
                    Forms\Components\DateTimePicker::make('fecha_entrega_real')
                        ->label('Entrega Real')->columnSpan(1),
                    Forms\Components\TextInput::make('numero_seguimiento')
                        ->label('N° de Seguimiento')->maxLength(50)->columnSpan(1),
                    Forms\Components\TextInput::make('firma_receptor')
                        ->label('Recibido por')->maxLength(150)->columnSpan(1),
                    Forms\Components\FileUpload::make('foto_entrega')
                        ->label('Foto de Entrega')->image()->directory('entregas')->columnSpan(2),
                    Forms\Components\Textarea::make('observaciones')
                        ->label('Observaciones')->rows(3)->columnSpanFull(),
                    Forms\Components\Textarea::make('motivo_fallo')
                        ->label('Motivo de Fallo (si aplica)')->rows(2)->columnSpanFull()
                        ->visible(fn (Forms\Get $get) => in_array($get('estado'), ['fallido', 'devuelto'])),
                ]),

            Forms\Components\Section::make('Seguimiento GPS')
                ->icon('heroicon-o-map-pin')
                ->columns(2)->collapsed()
                ->schema([
                    Forms\Components\TextInput::make('latitud_actual')
                        ->label('Latitud')->numeric()->step(0.00000001),
                    Forms\Components\TextInput::make('longitud_actual')
                        ->label('Longitud')->numeric()->step(0.00000001),
                ]),
        ]);
    }
}
