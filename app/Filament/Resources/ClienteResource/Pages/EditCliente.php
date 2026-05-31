<?php

namespace App\Filament\Resources\ClienteResource\Pages;

use App\Filament\Resources\ClienteResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;

class EditCliente extends EditRecord
{
    protected static string $resource = ClienteResource::class;

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
            Forms\Components\Section::make('Datos del Cliente')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('codigo')
                        ->label('Código')
                        ->disabled()
                        ->dehydrated()
                        ->required()
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('nombre')
                        ->label('Nombre / Razón Comercial')
                        ->required()
                        ->maxLength(150)
                        ->columnSpan(2),

                    Forms\Components\TextInput::make('razon_social')
                        ->label('Razón Social Legal')
                        ->maxLength(200)
                        ->columnSpan(2),

                    Forms\Components\Select::make('tipo')
                        ->label('Tipo de Cliente')
                        ->options([
                            'minorista'   => 'Minorista',
                            'mayorista'   => 'Mayorista',
                            'corporativo' => 'Corporativo',
                        ])
                        ->required()
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('nit')
                        ->label('NIT')
                        ->maxLength(20)
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('dui')
                        ->label('DUI')
                        ->maxLength(15)
                        ->columnSpan(1),

                    Forms\Components\Select::make('estado')
                        ->options([
                            'activo'    => 'Activo',
                            'inactivo'  => 'Inactivo',
                            'bloqueado' => 'Bloqueado',
                        ])
                        ->required()
                        ->columnSpan(1),
                ]),

            Forms\Components\Section::make('Contacto')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('email')
                        ->label('Correo Electrónico')
                        ->email()
                        ->maxLength(150)
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('telefono')
                        ->label('Teléfono')
                        ->tel()
                        ->maxLength(20)
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('celular')
                        ->label('Celular / WhatsApp')
                        ->maxLength(20)
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('pais')
                        ->label('País')
                        ->maxLength(80)
                        ->columnSpan(1),
                ]),

            Forms\Components\Section::make('Condiciones Comerciales')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('limite_credito')
                        ->label('Límite de Crédito ($)')
                        ->numeric()
                        ->prefix('$')
                        ->step(0.01)
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('dias_credito')
                        ->label('Días de Crédito')
                        ->numeric()
                        ->minValue(0)
                        ->columnSpan(1),

                    Forms\Components\Textarea::make('notas')
                        ->label('Observaciones')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
