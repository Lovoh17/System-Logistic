<?php

namespace App\Filament\Resources\ProveedorResource\Pages;

use App\Filament\Resources\ProveedorResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;

class EditProveedor extends EditRecord
{
    protected static string $resource = ProveedorResource::class;

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
            Forms\Components\Section::make('Información Principal')
                ->icon('heroicon-o-building-storefront')->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('codigo')
                        ->label('Código')->disabled()->dehydrated()->required()->maxLength(20)->columnSpan(1),
                    Forms\Components\TextInput::make('nombre')
                        ->label('Nombre Comercial')->required()->maxLength(150)->columnSpan(2),
                    Forms\Components\TextInput::make('razon_social')
                        ->label('Razón Social')->maxLength(200)->columnSpan(2),
                    Forms\Components\Select::make('categoria')
                        ->label('Categoría')
                        ->options([
                            'general'       => 'General',
                            'materia_prima' => 'Materia Prima',
                            'servicios'     => 'Servicios',
                        ])
                        ->required()->columnSpan(1),
                    Forms\Components\TextInput::make('nit')->label('NIT')->maxLength(20)->columnSpan(1),
                    Forms\Components\TextInput::make('ruc')->label('RUC / Registro')->maxLength(20)->columnSpan(1),
                    Forms\Components\Select::make('estado')
                        ->options([
                            'activo'     => 'Activo',
                            'inactivo'   => 'Inactivo',
                            'suspendido' => 'Suspendido',
                        ])
                        ->required()->columnSpan(1),
                ]),

            Forms\Components\Section::make('Contacto')
                ->icon('heroicon-o-phone')->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('email')->label('Correo Electrónico')->email()->maxLength(150)->columnSpan(1),
                    Forms\Components\TextInput::make('telefono')->label('Teléfono')->tel()->maxLength(20)->columnSpan(1),
                    Forms\Components\TextInput::make('celular')->label('Celular / WhatsApp')->maxLength(20)->columnSpan(1),
                    Forms\Components\TextInput::make('contacto_nombre')->label('Nombre del Contacto')->maxLength(100)->columnSpan(1),
                    Forms\Components\TextInput::make('contacto_email')->label('Email del Contacto')->email()->maxLength(150)->columnSpan(1),
                    Forms\Components\TextInput::make('contacto_telefono')->label('Teléfono del Contacto')->maxLength(20)->columnSpan(1),
                ]),

            Forms\Components\Section::make('Ubicación')
                ->icon('heroicon-o-map-pin')->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('pais')->label('País')->maxLength(80)->columnSpan(1),
                    Forms\Components\TextInput::make('departamento')->label('Departamento')->maxLength(80)->columnSpan(1),
                    Forms\Components\TextInput::make('municipio')->label('Municipio')->maxLength(80)->columnSpan(1),
                    Forms\Components\Textarea::make('direccion')->label('Dirección')->rows(2)->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Condiciones Comerciales')
                ->icon('heroicon-o-currency-dollar')->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('tiempo_entrega_dias')
                        ->label('Tiempo de Entrega (días)')->numeric()->minValue(0)->columnSpan(1),
                    Forms\Components\TextInput::make('calificacion')
                        ->label('Calificación (0-5)')->numeric()->minValue(0)->maxValue(5)->step(0.5)->columnSpan(1),
                    Forms\Components\Textarea::make('notas')->label('Notas')->rows(3)->columnSpanFull(),
                ]),
        ]);
    }
}
