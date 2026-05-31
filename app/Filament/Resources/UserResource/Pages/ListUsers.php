<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('roles'))
            ->columns([
                TextColumn::make('id')
                    ->label('ID')->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('name')
                    ->label('Nombre')->searchable()->sortable()->weight('bold'),

                TextColumn::make('email')
                    ->label('Correo electrónico')->searchable()->sortable()
                    ->copyable()->icon('heroicon-m-envelope'),

                BadgeColumn::make('email_verified_at')
                    ->label('Verificado')
                    ->getStateUsing(fn ($record) => $record->email_verified_at ? 'Verificado' : 'No verificado')
                    ->colors([
                        'success' => 'Verificado',
                        'danger'  => 'No verificado',
                    ])
                    ->icon(fn ($record) => $record->email_verified_at ? 'heroicon-m-check-badge' : 'heroicon-m-x-circle'),

                TextColumn::make('rol')
                    ->label('Roles')->badge()->color('info')->separator(',')->searchable(),

                TextColumn::make('created_at')
                    ->label('Creado')->dateTime('d/m/Y H:i')->sortable()->toggleable(),

                TextColumn::make('updated_at')
                    ->label('Última actualización')->dateTime('d/m/Y H:i')->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('email_verified_at')
                    ->label('Usuarios verificados')
                    ->query(fn ($query) => $query->whereNotNull('email_verified_at')),

                Tables\Filters\Filter::make('unverified')
                    ->label('Usuarios no verificados')
                    ->query(fn ($query) => $query->whereNull('email_verified_at')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Ver')->icon('heroicon-m-eye'),
                Tables\Actions\EditAction::make()->label('Editar')->icon('heroicon-m-pencil-square'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
