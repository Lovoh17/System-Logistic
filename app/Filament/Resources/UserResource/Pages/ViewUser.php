<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

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
            Infolists\Components\Section::make('Información del Usuario')
                ->columns(2)
                ->schema([
                    Infolists\Components\TextEntry::make('name')
                        ->label('Nombre completo')->weight('bold'),
                    Infolists\Components\TextEntry::make('email')
                        ->label('Correo electrónico')->copyable(),
                    Infolists\Components\TextEntry::make('rol')
                        ->label('Roles')->badge()->color('info'),
                    Infolists\Components\TextEntry::make('email_verified_at')
                        ->label('Verificado')
                        ->getStateUsing(fn ($record) => $record->email_verified_at ? 'Verificado' : 'No verificado')
                        ->badge()
                        ->color(fn ($state) => $state === 'Verificado' ? 'success' : 'danger'),
                    Infolists\Components\TextEntry::make('created_at')
                        ->label('Fecha de creación')->dateTime('d/m/Y H:i'),
                    Infolists\Components\TextEntry::make('updated_at')
                        ->label('Última actualización')->dateTime('d/m/Y H:i'),
                ]),
        ]);
    }
}
