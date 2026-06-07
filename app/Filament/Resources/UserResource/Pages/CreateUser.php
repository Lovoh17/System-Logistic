<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Información del Usuario')
                ->icon('heroicon-o-user-circle')->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Nombre completo')->required()->maxLength(255)
                        ->placeholder('Ej: Juan Pérez'),

                    TextInput::make('email')
                        ->label('Correo electrónico')->email()->required()->maxLength(255)
                        ->unique(ignoreRecord: true)->placeholder('ejemplo@tracelog.com'),

                    TextInput::make('password')
                        ->label('Contraseña')->password()->required()->maxLength(255)
                        ->dehydrated(fn ($state) => filled($state))
                        ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                        ->helperText('Mínimo 8 caracteres.'),

                    DateTimePicker::make('email_verified_at')
                        ->label('Verificación de email')->default(now())
                        ->helperText('Fecha y hora en que se verificó el correo'),
                ]),

            Forms\Components\Section::make('Roles y Permisos')
                ->icon('heroicon-o-shield-check')
                ->schema([
                    Select::make('roles')
                        ->label('Roles del usuario')
                        ->relationship('roles', 'name')
                        ->multiple()->preload()->searchable()
                        ->helperText('Selecciona los roles que tendrá este usuario'),
                ]),
        ]);
    }
}
