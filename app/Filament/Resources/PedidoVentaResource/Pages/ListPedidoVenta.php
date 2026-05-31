<?php

namespace App\Filament\Resources\PedidoVentaResource\Pages;

use App\Filament\Resources\PedidoVentaResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Tables\Table;

class ListPedidoVenta extends ListRecords
{
    protected static string $resource = PedidoVentaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['cliente', 'almacen']))
            ->columns([
                Tables\Columns\TextColumn::make('numero')
                    ->label('N° Pedido')
                    ->searchable()->sortable()
                    ->badge()->color('primary'),

                Tables\Columns\TextColumn::make('cliente.nombre')
                    ->label('Cliente')
                    ->searchable()->sortable(),

                Tables\Columns\TextColumn::make('fecha_pedido')
                    ->label('Fecha')
                    ->date('d/m/Y')->sortable(),

                Tables\Columns\TextColumn::make('fecha_requerida')
                    ->label('Requerido')
                    ->date('d/m/Y')->sortable()
                    ->color(fn ($record) => $record->fecha_requerida && $record->fecha_requerida->isPast() && $record->estado !== 'entregado' ? 'danger' : null),

                Tables\Columns\BadgeColumn::make('prioridad')
                    ->colors([
                        'gray'    => 'baja',
                        'info'    => 'normal',
                        'warning' => 'alta',
                        'danger'  => 'urgente',
                    ]),

                Tables\Columns\TextColumn::make('almacen.nombre')
                    ->label('Sucursal')
                    ->searchable()->sortable()
                    ->badge()->color('gray')->toggleable(),

                Tables\Columns\BadgeColumn::make('estado')
                    ->colors([
                        'gray'    => 'borrador',
                        'info'    => 'confirmado',
                        'warning' => 'en_preparacion',
                        'primary' => 'listo',
                        'indigo'  => 'en_transito',
                        'success' => 'entregado',
                        'danger'  => 'cancelado',
                    ]),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('USD')->sortable()->alignRight(),

                Tables\Columns\TextColumn::make('canal_venta')
                    ->label('Canal')
                    ->badge()->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->options([
                        'borrador'       => 'Borrador',
                        'confirmado'     => 'Confirmado',
                        'en_preparacion' => 'En Preparación',
                        'listo'          => 'Listo',
                        'en_transito'    => 'En Tránsito',
                        'entregado'      => 'Entregado',
                        'cancelado'      => 'Cancelado',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('prioridad')
                    ->options([
                        'baja'    => 'Baja',
                        'normal'  => 'Normal',
                        'alta'    => 'Alta',
                        'urgente' => 'Urgente',
                    ]),

                Tables\Filters\SelectFilter::make('almacen_id')
                    ->label('Sucursal')
                    ->relationship('almacen', 'nombre')
                    ->multiple(),

                Tables\Filters\Filter::make('fecha_pedido')
                    ->form([
                        Forms\Components\DatePicker::make('desde')->label('Desde'),
                        Forms\Components\DatePicker::make('hasta')->label('Hasta'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['desde'], fn ($q, $d) => $q->whereDate('fecha_pedido', '>=', $d))
                            ->when($data['hasta'], fn ($q, $d) => $q->whereDate('fecha_pedido', '<=', $d));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('generar_envio')
                    ->label('Crear Envío')
                    ->icon('heroicon-o-truck')
                    ->color('success')
                    ->visible(fn ($record) => in_array($record->estado, ['listo', 'confirmado']))
                    ->url(fn ($record) => route('filament.admin.resources.envios.create', ['pedido_venta_id' => $record->id])),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
