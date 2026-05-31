<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

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
            Forms\Components\Section::make('Información del Usuario')
                ->icon('heroicon-o-user-circle')->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Nombre completo')->required()->maxLength(255),

                    TextInput::make('email')
                        ->label('Correo electrónico')->email()->required()->maxLength(255)
                        ->unique(ignoreRecord: true),

                    TextInput::make('password')
                        ->label('Contraseña')->password()->maxLength(255)
                        ->dehydrated(fn ($state) => filled($state))
                        ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                        ->helperText('Dejar en blanco para mantener la actual.'),

                    DateTimePicker::make('email_verified_at')
                        ->label('Verificación de email'),
                ]),

            Forms\Components\Section::make('Roles y Permisos')
                ->icon('heroicon-o-shield-check')
                ->schema([
                    Select::make('roles')
                        ->label('Roles del usuario')
                        ->relationship('roles', 'name')
                        ->multiple()->preload()->searchable(),
                ]),
        ]);
    }
}
