<?php

namespace App\Filament\Resources\TransportistaResource\Pages;

use App\Filament\Resources\TransportistaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Tables\Table;

class ListTransportistas extends ListRecords
{
    protected static string $resource = TransportistaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('codigo')
                    ->badge()->color('gray'),
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable()->sortable()
                    ->description(fn ($record) => ucfirst($record->tipo)),
                Tables\Columns\TextColumn::make('vehiculo_tipo')
                    ->label('Vehículo')->badge()->color('info'),
                Tables\Columns\TextColumn::make('vehiculo_placa')
                    ->label('Placa'),
                Tables\Columns\TextColumn::make('conductor_nombre')
                    ->label('Conductor')->searchable()->toggleable(),
                Tables\Columns\IconColumn::make('tiene_gps')
                    ->label('GPS')->boolean(),
                Tables\Columns\IconColumn::make('tiene_refrigeracion')
                    ->label('❄️')->boolean(),
                Tables\Columns\TextColumn::make('capacidad_kg')
                    ->label('Cap. kg')->toggleable(),
                Tables\Columns\BadgeColumn::make('estado')
                    ->colors([
                        'success' => 'disponible',
                        'warning' => 'en_ruta',
                        'danger'  => 'mantenimiento',
                        'gray'    => 'inactivo',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->options([
                        'disponible'    => 'Disponible',
                        'en_ruta'       => 'En Ruta',
                        'mantenimiento' => 'Mantenimiento',
                    ]),
                Tables\Filters\SelectFilter::make('tipo')
                    ->options(['propio' => 'Propio', 'externo' => 'Externo']),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ]);
    }
}
