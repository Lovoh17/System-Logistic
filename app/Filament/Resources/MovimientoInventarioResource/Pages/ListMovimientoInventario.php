<?php

namespace App\Filament\Resources\MovimientoInventarioResource\Pages;

use App\Filament\Resources\MovimientoInventarioResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Tables\Table;

class ListMovimientoInventario extends ListRecords
{
    protected static string $resource = MovimientoInventarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Ajuste Manual'),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['producto', 'user', 'almacen']))
            ->columns([
                Tables\Columns\TextColumn::make('numero')
                    ->label('N° Mov.')
                    ->searchable()->sortable()
                    ->badge()->color('gray'),

                Tables\Columns\TextColumn::make('fecha_movimiento')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')->sortable(),

                Tables\Columns\TextColumn::make('producto.codigo')
                    ->label('Cód.')
                    ->badge()->color('gray'),

                Tables\Columns\TextColumn::make('producto.nombre')
                    ->label('Producto')
                    ->searchable()->sortable(),

                Tables\Columns\TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'entrada_compra'     => 'Entrada Compra',
                        'salida_venta'       => 'Salida Venta',
                        'ajuste_positivo'    => '⬆ Ajuste (+)',
                        'ajuste_negativo'    => '⬇ Ajuste (-)',
                        'traslado_entrada'   => 'Traslado Entrada',
                        'traslado_salida'    => 'Traslado Salida',
                        'devolucion_compra'  => 'Dev. Compra',
                        'devolucion_venta'   => 'Dev. Venta',
                        'merma'              => 'Merma',
                        'inventario_inicial' => 'Inv. Inicial',
                        default              => $state,
                    })
                    ->color(function ($record) {
                        try {
                            return $record->tipo_color;
                        } catch (\Throwable $e) {
                            \Log::error('[Kardex] tipo_color falló', [
                                'movimiento_id' => $record->id,
                                'tipo'          => $record->tipo,
                                'error'         => $e->getMessage(),
                            ]);
                            return 'gray';
                        }
                    }),

                Tables\Columns\TextColumn::make('cantidad')
                    ->label('Cantidad')
                    ->alignCenter()
                    ->formatStateUsing(function ($state, $record) {
                        try {
                            return ($record->es_entrada ? '+' : '-') . number_format($state, 0);
                        } catch (\Throwable $e) {
                            \Log::error('[Kardex] es_entrada falló', [
                                'movimiento_id' => $record->id,
                                'error'         => $e->getMessage(),
                            ]);
                            return number_format($state, 0);
                        }
                    })
                    ->color(function ($record) {
                        try {
                            return $record->es_entrada ? 'success' : 'danger';
                        } catch (\Throwable $e) {
                            return 'gray';
                        }
                    }),

                Tables\Columns\TextColumn::make('stock_nuevo')
                    ->label('Stock Nuevo')
                    ->alignCenter()->badge()
                    ->color(function ($record) {
                        try {
                            if (!$record->producto) {
                                \Log::warning('[Kardex] stock_color: producto NULL', [
                                    'movimiento_id' => $record->id,
                                    'producto_id'   => $record->producto_id,
                                ]);
                                return 'gray';
                            }
                            return $record->producto->stock_color;
                        } catch (\Throwable $e) {
                            \Log::error('[Kardex] stock_color falló', [
                                'movimiento_id' => $record->id,
                                'producto_id'   => $record->producto_id,
                                'error'         => $e->getMessage(),
                            ]);
                            return 'gray';
                        }
                    }),

                Tables\Columns\TextColumn::make('costo_total')
                    ->label('Valor')->money('USD')->toggleable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('motivo')
                    ->label('Motivo')->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tipo')
                    ->options([
                        'entrada_compra'     => 'Entrada Compra',
                        'salida_venta'       => 'Salida Venta',
                        'ajuste_positivo'    => 'Ajuste Positivo',
                        'ajuste_negativo'    => 'Ajuste Negativo',
                        'merma'              => 'Merma',
                        'inventario_inicial' => 'Inventario Inicial',
                    ])->multiple(),

                Tables\Filters\SelectFilter::make('producto_id')
                    ->label('Producto')
                    ->relationship('producto', 'nombre')
                    ->searchable()->preload(),

                Tables\Filters\Filter::make('fecha')
                    ->form([
                        Forms\Components\DatePicker::make('desde')->label('Desde'),
                        Forms\Components\DatePicker::make('hasta')->label('Hasta'),
                    ])
                    ->query(fn ($query, array $data) =>
                        $query
                            ->when($data['desde'], fn ($q, $d) => $q->whereDate('fecha_movimiento', '>=', $d))
                            ->when($data['hasta'], fn ($q, $d) => $q->whereDate('fecha_movimiento', '<=', $d))
                    ),
            ])
            ->defaultSort('fecha_movimiento', 'desc')
            ->paginated([25, 50, 100])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }
}
