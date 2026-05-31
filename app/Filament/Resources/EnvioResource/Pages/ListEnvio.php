<?php

namespace App\Filament\Resources\EnvioResource\Pages;

use App\Filament\Resources\EnvioResource;
use App\Models\Envio;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Tables\Table;

class ListEnvio extends ListRecords
{
    protected static string $resource = EnvioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['pedidoVenta.cliente', 'transportista']))
            ->columns([
                Tables\Columns\TextColumn::make('numero')
                    ->label('N° Envío')
                    ->searchable()->sortable()
                    ->badge()->color('primary'),

                Tables\Columns\TextColumn::make('pedidoVenta.numero')
                    ->label('Pedido')
                    ->searchable()
                    ->badge()->color('gray'),

                Tables\Columns\TextColumn::make('pedidoVenta.cliente.nombre')
                    ->label('Cliente')
                    ->searchable(),

                Tables\Columns\TextColumn::make('transportista.nombre')
                    ->label('Transportista')
                    ->searchable()->toggleable(),

                Tables\Columns\TextColumn::make('destino_municipio')
                    ->label('Destino')
                    ->description(fn ($record) => $record->destino_departamento),

                Tables\Columns\TextColumn::make('fecha_programada')
                    ->label('Programado')
                    ->date('d/m/Y')->sortable(),

                Tables\Columns\TextColumn::make('fecha_entrega_estimada')
                    ->label('Est. Entrega')
                    ->dateTime('d/m/Y H:i')->sortable()->toggleable(),

                Tables\Columns\BadgeColumn::make('estado')
                    ->colors([
                        'gray'    => 'programado',
                        'warning' => 'en_preparacion',
                        'info'    => 'despachado',
                        'primary' => 'en_transito',
                        'indigo'  => 'en_destino',
                        'success' => 'entregado',
                        'danger'  => 'fallido',
                        'orange'  => 'devuelto',
                    ]),

                Tables\Columns\TextColumn::make('costo_envio')
                    ->label('Costo')
                    ->money('USD')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->options([
                        'programado'     => 'Programado',
                        'en_preparacion' => 'En Preparación',
                        'despachado'     => 'Despachado',
                        'en_transito'    => 'En Tránsito',
                        'en_destino'     => 'En Destino',
                        'entregado'      => 'Entregado',
                        'fallido'        => 'Fallido',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('transportista_id')
                    ->label('Transportista')
                    ->relationship('transportista', 'nombre'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('registrar_seguimiento')
                    ->label('Registrar Evento')
                    ->icon('heroicon-o-map-pin')
                    ->color('info')
                    ->form([
                        Forms\Components\TextInput::make('evento')
                            ->label('Evento')
                            ->required()
                            ->placeholder('Ej: Salida de bodega, Llegada a destino'),
                        Forms\Components\TextInput::make('ubicacion')
                            ->label('Ubicación'),
                        Forms\Components\DateTimePicker::make('fecha_hora')
                            ->label('Fecha y Hora')
                            ->default(now())
                            ->required(),
                        Forms\Components\Textarea::make('descripcion')
                            ->label('Descripción')
                            ->rows(2),
                    ])
                    ->action(function (Envio $record, array $data) {
                        $record->seguimientos()->create([
                            ...$data,
                            'responsable' => auth()->user()->name,
                        ]);
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
