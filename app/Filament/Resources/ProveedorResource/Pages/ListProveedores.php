<?php

namespace App\Filament\Resources\ProveedorResource\Pages;

use App\Filament\Resources\ProveedorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Tables\Table;

class ListProveedores extends ListRecords
{
    protected static string $resource = ProveedorResource::class;

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
                    ->label('Código')->searchable()->sortable()->badge()->color('gray'),

                Tables\Columns\TextColumn::make('nombre')
                    ->label('Proveedor')->searchable()->sortable()
                    ->description(fn ($record) => $record->categoria_label),

                Tables\Columns\TextColumn::make('contacto_nombre')
                    ->label('Contacto')->searchable()->toggleable(),

                Tables\Columns\TextColumn::make('telefono')
                    ->label('Teléfono')->searchable()->toggleable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('tiempo_entrega_dias')
                    ->label('Entrega')->sortable()->suffix(' días')->alignCenter(),

                Tables\Columns\TextColumn::make('calificacion')
                    ->label('Calif.')->sortable()->alignCenter()->badge()
                    ->color(fn ($state) => match (true) {
                        $state >= 4  => 'success',
                        $state >= 2  => 'warning',
                        default      => 'danger',
                    }),

                Tables\Columns\TextColumn::make('productos_count')
                    ->label('Productos')->counts('productos')
                    ->alignCenter()->badge()->color('info'),

                Tables\Columns\BadgeColumn::make('estado')
                    ->label('Estado')
                    ->colors([
                        'success' => 'activo',
                        'gray'    => 'inactivo',
                        'danger'  => 'suspendido',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->options([
                        'activo'     => 'Activo',
                        'inactivo'   => 'Inactivo',
                        'suspendido' => 'Suspendido',
                    ]),
                Tables\Filters\SelectFilter::make('categoria')
                    ->options([
                        'general'       => 'General',
                        'materia_prima' => 'Materia Prima',
                        'servicios'     => 'Servicios',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('nombre');
    }
}
