<?php

namespace App\Filament\Resources\TrasladoResource\Pages;

use App\Filament\Resources\TrasladoResource;
use App\Models\InventarioAlmacen;
use App\Models\Transportista;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Tables\Table;

class ListTraslados extends ListRecords
{
    protected static string $resource = TrasladoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['producto', 'almacenOrigen', 'almacenDestino', 'transportista']))
            ->columns([
                Tables\Columns\TextColumn::make('numero')
                    ->label('N° Traslado')->searchable()->sortable()
                    ->badge()->color('primary'),
                Tables\Columns\TextColumn::make('producto.nombre')
                    ->label('Producto')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('almacenOrigen.nombre')
                    ->label('Origen')->badge()->color('gray'),
                Tables\Columns\TextColumn::make('almacenDestino.nombre')
                    ->label('Destino')->badge()->color('gray'),
                Tables\Columns\TextColumn::make('cantidad')
                    ->label('Cantidad')->numeric(3)->sortable(),
                Tables\Columns\TextColumn::make('transportista.nombre')
                    ->label('Transportista')->searchable(),
                Tables\Columns\TextColumn::make('fecha_programada')
                    ->label('Fecha Prog.')->date('d/m/Y')->sortable(),
                Tables\Columns\BadgeColumn::make('estado')
                    ->label('Estado')
                    ->colors([
                        'warning' => 'pendiente',
                        'info'    => 'asignado',
                        'primary' => 'en_transito',
                        'success' => 'entregado',
                        'danger'  => 'cancelado',
                    ])
                    ->icons([
                        'heroicon-m-clock'        => 'pendiente',
                        'heroicon-m-truck'         => 'en_transito',
                        'heroicon-m-check-circle'  => 'entregado',
                        'heroicon-m-x-circle'      => 'cancelado',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->options([
                        'pendiente'   => 'Pendiente',
                        'asignado'    => 'Asignado',
                        'en_transito' => 'En Tránsito',
                        'entregado'   => 'Entregado',
                        'cancelado'   => 'Cancelado',
                    ]),
                Tables\Filters\SelectFilter::make('almacen_origen_id')
                    ->label('Sucursal Origen')
                    ->relationship('almacenOrigen', 'nombre'),
                Tables\Filters\SelectFilter::make('almacen_destino_id')
                    ->label('Sucursal Destino')
                    ->relationship('almacenDestino', 'nombre'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('asignar_transporte')
                    ->label('Asignar Transporte')
                    ->icon('heroicon-m-truck')->color('info')
                    ->visible(fn ($record) => $record->estado === 'pendiente')
                    ->form([
                        Forms\Components\Select::make('transportista_id')
                            ->label('Transportista')
                            ->options(Transportista::where('estado', 'disponible')->pluck('nombre', 'id'))
                            ->required(),
                        Forms\Components\DatePicker::make('fecha_salida')
                            ->label('Fecha de Salida')->default(now()),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'transportista_id' => $data['transportista_id'],
                            'fecha_salida'     => $data['fecha_salida'],
                            'estado'           => 'asignado',
                            'asignado_por'     => auth()->id(),
                        ]);
                        Notification::make()->title('Transporte asignado')->success()->send();
                    }),

                Tables\Actions\Action::make('iniciar_transito')
                    ->label('Iniciar Tránsito')
                    ->icon('heroicon-m-play')->color('primary')
                    ->visible(fn ($record) => $record->estado === 'asignado')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['estado' => 'en_transito']);
                        Notification::make()->title('Traslado en tránsito')->success()->send();
                    }),

                Tables\Actions\Action::make('completar_entrega')
                    ->label('Completar Entrega')
                    ->icon('heroicon-m-check-circle')->color('success')
                    ->visible(fn ($record) => $record->estado === 'en_transito')
                    ->form([
                        Forms\Components\DatePicker::make('fecha_entrega_real')
                            ->label('Fecha de Entrega')->default(now())->required(),
                        Forms\Components\TextInput::make('cantidad_recibida')
                            ->label('Cantidad Recibida')->numeric()
                            ->default(fn ($record) => $record->cantidad)
                            ->required()->minValue(0)
                            ->maxValue(fn ($record) => $record->cantidad),
                    ])
                    ->action(function ($record, array $data) {
                        $inventarioDestino = InventarioAlmacen::where('producto_id', $record->producto_id)
                            ->where('almacen_id', $record->almacen_destino_id)->first();

                        if ($inventarioDestino) {
                            $inventarioDestino->stock_actual += $data['cantidad_recibida'];
                            $inventarioDestino->save();
                        } else {
                            InventarioAlmacen::create([
                                'producto_id' => $record->producto_id,
                                'almacen_id'  => $record->almacen_destino_id,
                                'stock_actual' => $data['cantidad_recibida'],
                                'stock_minimo' => 0,
                                'stock_maximo' => 999999,
                            ]);
                        }

                        $record->update([
                            'estado'              => 'entregado',
                            'fecha_entrega_real'  => $data['fecha_entrega_real'],
                            'cantidad_recibida'   => $data['cantidad_recibida'],
                        ]);

                        Notification::make()->title('Entrega completada')->success()->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
