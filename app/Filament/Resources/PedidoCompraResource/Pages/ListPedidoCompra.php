<?php

namespace App\Filament\Resources\PedidoCompraResource\Pages;

use App\Filament\Pages\RecomendacionesCompra;
use App\Filament\Resources\PedidoCompraResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Tables\Table;

class ListPedidoCompra extends ListRecords
{
    protected static string $resource = PedidoCompraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            Actions\Action::make('recomendaciones_compra')
                ->label('Recomendaciones de Compra')
                ->icon('heroicon-o-light-bulb')
                ->color('warning')
                ->url(fn() => RecomendacionesCompra::getUrl()),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['proveedor', 'user'])->withCount('items'))
            ->columns([
                Tables\Columns\TextColumn::make('numero')
                    ->label('N° OC')
                    ->searchable()->sortable()
                    ->badge()->color('primary'),

                Tables\Columns\TextColumn::make('proveedor.nombre')
                    ->label('Proveedor')
                    ->searchable()->sortable(),

                Tables\Columns\TextColumn::make('fecha_pedido')
                    ->label('Fecha Pedido')
                    ->date('d/m/Y')->sortable(),

                Tables\Columns\TextColumn::make('fecha_requerida')
                    ->label('Fecha Req.')
                    ->date('d/m/Y')->sortable()
                    ->color(fn ($record) =>
                        $record->fecha_requerida &&
                        $record->fecha_requerida->isPast() &&
                        !in_array($record->estado, ['recibido', 'cancelado'])
                            ? 'danger' : null
                    ),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('Ítems')
                    ->counts('items')
                    ->badge()->color('gray')->alignCenter(),

                Tables\Columns\BadgeColumn::make('estado')
                    ->colors([
                        'gray'    => 'borrador',
                        'info'    => 'enviado',
                        'primary' => 'confirmado',
                        'warning' => 'parcial',
                        'success' => 'recibido',
                        'danger'  => 'cancelado',
                    ]),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('USD')->sortable()->alignRight(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Creado por')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->options([
                        'borrador'   => 'Borrador',
                        'enviado'    => 'Enviado',
                        'confirmado' => 'Confirmado',
                        'parcial'    => 'Parcial',
                        'recibido'   => 'Recibido',
                        'cancelado'  => 'Cancelado',
                    ])->multiple(),

                Tables\Filters\Filter::make('fecha_pedido')
                    ->form([
                        Forms\Components\DatePicker::make('desde')->label('Desde'),
                        Forms\Components\DatePicker::make('hasta')->label('Hasta'),
                    ])
                    ->query(fn ($query, array $data) =>
                        $query
                            ->when($data['desde'], fn ($q, $d) => $q->whereDate('fecha_pedido', '>=', $d))
                            ->when($data['hasta'], fn ($q, $d) => $q->whereDate('fecha_pedido', '<=', $d))
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('enviar_proveedor')
                    ->label('Enviar')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->visible(fn ($record) => $record->estado === 'borrador')
                    ->requiresConfirmation()
                    ->modalHeading('¿Enviar Orden de Compra al Proveedor?')
                    ->modalDescription('Se marcará como enviada y se notificará al proveedor.')
                    ->action(function ($record) {
                        $record->update(['estado' => 'enviado']);
                        Notification::make()->title('OC enviada al proveedor')->success()->send();
                    }),

                Tables\Actions\Action::make('confirmar_recepcion')
                    ->label('Recibir')
                    ->icon('heroicon-o-inbox-arrow-down')
                    ->color('success')
                    ->visible(fn ($record) => in_array($record->estado, ['enviado', 'confirmado', 'parcial']))
                    ->form([
                        Forms\Components\DatePicker::make('fecha_recepcion')
                            ->label('Fecha de Recepción')
                            ->default(now())->required(),
                        Forms\Components\Textarea::make('notas_recepcion')
                            ->label('Notas de recepción')->rows(2),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'estado'          => 'recibido',
                            'fecha_recepcion' => $data['fecha_recepcion'],
                        ]);
                        Notification::make()
                            ->title('Recepción registrada')
                            ->body('El inventario ha sido actualizado.')
                            ->success()->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
