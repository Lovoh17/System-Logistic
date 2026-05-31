<?php

namespace App\Filament\Resources\TrasladoResource\Pages;

use App\Filament\Resources\TrasladoResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;

class EditTraslado extends EditRecord
{
    protected static string $resource = TrasladoResource::class;

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
            Forms\Components\Section::make('Información del Traslado')
                ->columns(4)
                ->schema([
                    Forms\Components\TextInput::make('numero')
                        ->label('N° Traslado')->disabled()->dehydrated()->required()->columnSpan(1),

                    Forms\Components\Select::make('producto_id')
                        ->label('Producto')
                        ->options(\App\Models\Producto::activo()->pluck('nombre', 'id'))
                        ->searchable()->preload()->required()->columnSpan(2),

                    Forms\Components\Select::make('almacen_origen_id')
                        ->label('Sucursal Origen')
                        ->options(\App\Models\Almacen::where('activo', true)->pluck('nombre', 'id'))
                        ->required()->columnSpan(1),

                    Forms\Components\Select::make('almacen_destino_id')
                        ->label('Sucursal Destino')
                        ->options(\App\Models\Almacen::where('activo', true)->pluck('nombre', 'id'))
                        ->required()->searchable()->columnSpan(1),
                ]),

            Forms\Components\Section::make('Cantidades y Fechas')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('cantidad')
                        ->label('Cantidad')->numeric()->required()->minValue(1)->columnSpan(1),

                    Forms\Components\Select::make('transportista_id')
                        ->label('Transportista')
                        ->options(\App\Models\Transportista::where('estado', 'disponible')->pluck('nombre', 'id'))
                        ->searchable()->preload()->columnSpan(2),

                    Forms\Components\DatePicker::make('fecha_programada')
                        ->label('Fecha Programada')->required()->columnSpan(1),

                    Forms\Components\DatePicker::make('fecha_entrega_estimada')
                        ->label('Fecha Estimada de Entrega')->columnSpan(1),
                ]),

            Forms\Components\Section::make('Información Adicional')
                ->schema([
                    Forms\Components\Textarea::make('motivo')
                        ->label('Motivo del Traslado')->rows(2)->required(),
                    Forms\Components\Textarea::make('observaciones')
                        ->label('Observaciones')->rows(2),
                ]),
        ]);
    }
}
