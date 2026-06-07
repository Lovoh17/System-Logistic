<?php

namespace App\Filament\Resources\InventarioAlmacenResource\Pages;

use App\Filament\Resources\InventarioAlmacenResource;
use App\Models\Almacen;
use App\Models\InventarioAlmacen;
use App\Exports\InventarioSucursalExport;
use App\Exports\InventarioGeneralExport;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Forms;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Table;

class ListInventarioAlmacens extends ListRecords
{
    protected static string $resource = InventarioAlmacenResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['producto', 'almacen']))
            ->columns([
                TextColumn::make('producto.imagen')
                    ->label('')
                    ->getStateUsing(fn ($record) => $record->producto?->imagen)
                    ->formatStateUsing(fn ($state) => $state ?
                        '<img src="' . asset('storage/' . $state) . '" class="w-10 h-10 rounded-full object-cover">' :
                        '<div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-500">📦</div>'
                    )
                    ->html()->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('producto.codigo')
                    ->label('Código')->searchable()->sortable()
                    ->badge()->color('gray')->toggleable(),

                TextColumn::make('producto.nombre')
                    ->label('Producto')->searchable()->sortable()
                    ->description(fn ($record) => $record->producto?->categoria?->nombre)->toggleable(),

                TextColumn::make('almacen.nombre')
                    ->label('Sucursal')->searchable()->sortable()->badge()
                    ->color(fn ($record) => match ($record->almacen?->nombre) {
                        'San Salvador (Central)' => 'primary',
                        default                  => 'gray',
                    })->toggleable(),

                TextColumn::make('stock_actual')
                    ->label('Stock Actual')->numeric(3)->sortable()
                    ->color(fn ($record) => match (true) {
                        $record->stock_actual >= $record->stock_maximo => 'warning',
                        $record->stock_actual <= $record->stock_minimo => 'danger',
                        default                                        => 'success',
                    })
                    ->formatStateUsing(fn ($state, $record) =>
                        number_format($state, 2) . ' ' . ($record->producto?->unidad_medida ?? 'unid')
                    )->toggleable(),

                TextColumn::make('stock_minimo')
                    ->label('Mínimo')->numeric(3)->sortable()
                    ->formatStateUsing(fn ($state, $record) =>
                        number_format($state, 2) . ' ' . ($record->producto?->unidad_medida ?? 'unid')
                    )->toggleable(),

                TextColumn::make('stock_maximo')
                    ->label('Máximo')->numeric(3)->sortable()
                    ->formatStateUsing(fn ($state, $record) =>
                        number_format($state, 2) . ' ' . ($record->producto?->unidad_medida ?? 'unid')
                    )->toggleable(),

                TextColumn::make('punto_reorden')
                    ->label('Punto Reorden')->numeric(3)->sortable()
                    ->formatStateUsing(fn ($state, $record) =>
                        number_format($state, 2) . ' ' . ($record->producto?->unidad_medida ?? 'unid')
                    )->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('diferencia')
                    ->label('Estado Stock')
                    ->getStateUsing(fn ($record) => match (true) {
                        $record->stock_actual > $record->stock_maximo =>
                            'Excede en ' . number_format($record->stock_actual - $record->stock_maximo, 2),
                        $record->stock_actual < $record->stock_minimo =>
                            'Falta ' . number_format($record->stock_minimo - $record->stock_actual, 2),
                        default => 'Óptimo',
                    })
                    ->badge()
                    ->color(fn ($record) => match (true) {
                        $record->stock_actual > $record->stock_maximo => 'warning',
                        $record->stock_actual < $record->stock_minimo => 'danger',
                        default                                        => 'success',
                    })->toggleable(),

                BadgeColumn::make('estado')
                    ->label('Nivel')
                    ->getStateUsing(fn ($record) => match (true) {
                        $record->stock_actual >= $record->stock_maximo => 'Sobrestock',
                        $record->stock_actual <= $record->stock_minimo => 'Stock Bajo',
                        default                                        => 'Óptimo',
                    })
                    ->colors([
                        'warning' => 'Sobrestock',
                        'danger'  => 'Stock Bajo',
                        'success' => 'Óptimo',
                    ])->toggleable(),

                TextColumn::make('porcentaje_ocupacion')
                    ->label('Ocupación')
                    ->getStateUsing(fn ($record) => $record->stock_maximo > 0
                        ? round(($record->stock_actual / $record->stock_maximo) * 100, 1) . '%'
                        : '0%'
                    )
                    ->badge()
                    ->color(fn ($state) =>
                        (float) str_replace('%', '', $state) >= 90 ? 'danger' :
                        ((float) str_replace('%', '', $state) >= 70 ? 'warning' : 'success')
                    )->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('almacen_id')
                    ->label('Filtrar por Sucursal')
                    ->placeholder('Todas las sucursales')
                    ->options(Almacen::where('activo', true)->pluck('nombre', 'id'))
                    ->multiple()->preload()->searchable(),

                SelectFilter::make('estado_stock')
                    ->label('Estado de Stock')
                    ->placeholder('Todos los estados')
                    ->options([
                        'sobrestock' => 'Sobrestock (Excede máximo)',
                        'bajo'       => 'Stock Bajo (Debajo mínimo)',
                        'optimo'     => 'Óptimo',
                        'critico'    => 'Crítico (Menos del 50% del mínimo)',
                    ])
                    ->query(function ($query, $data) {
                        if (empty($data['value'])) return $query;
                        return match ($data['value']) {
                            'sobrestock' => $query->whereColumn('stock_actual', '>=', 'stock_maximo'),
                            'bajo'       => $query->whereColumn('stock_actual', '<=', 'stock_minimo'),
                            'optimo'     => $query->whereColumn('stock_actual', '>', 'stock_minimo')
                                                  ->whereColumn('stock_actual', '<', 'stock_maximo'),
                            'critico'    => $query->whereColumn('stock_actual', '<=', 'stock_minimo / 2'),
                            default      => $query,
                        };
                    }),

                Filter::make('rango_stock')
                    ->form([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('stock_desde')->label('Stock desde')->numeric()->placeholder('0'),
                            Forms\Components\TextInput::make('stock_hasta')->label('Stock hasta')->numeric()->placeholder('100'),
                        ]),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['stock_desde'], fn ($q, $v) => $q->where('stock_actual', '>=', $v))
                            ->when($data['stock_hasta'], fn ($q, $v) => $q->where('stock_actual', '<=', $v));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['stock_desde'] ?? null) $indicators['stock_desde'] = "Stock ≥ {$data['stock_desde']}";
                        if ($data['stock_hasta'] ?? null) $indicators['stock_hasta'] = "Stock ≤ {$data['stock_hasta']}";
                        return $indicators;
                    }),

                SelectFilter::make('categoria_id')
                    ->label('Categoría')->placeholder('Todas las categorías')
                    ->relationship('producto.categoria', 'nombre')->preload()->searchable(),

                SelectFilter::make('proveedor_id')
                    ->label('Proveedor')->placeholder('Todos los proveedores')
                    ->relationship('producto.proveedor', 'nombre')->preload()->searchable(),
            ])
            ->actions([
                Tables\Actions\Action::make('rebalancear')
                    ->label('Sugerir Traslado')
                    ->icon('heroicon-m-arrow-path')->color('warning')
                    ->visible(fn ($record) => $record->stock_actual > $record->stock_maximo)
                    ->modalHeading('Sugerir Traslado por Excedente')
                    ->modalDescription(fn ($record) =>
                        "El producto {$record->producto->nombre} tiene un excedente de " .
                        number_format($record->stock_actual - $record->stock_maximo, 2) .
                        " unidades en {$record->almacen->nombre}"
                    )
                    ->form(fn ($record) => static::getTrasladoForm($record))
                    ->action(fn ($record, array $data) => static::procesarTraslado($record, $data)),

                Tables\Actions\Action::make('solicitar_compra')
                    ->label('Solicitar Compra')
                    ->icon('heroicon-m-shopping-cart')->color('danger')
                    ->visible(fn ($record) => $record->stock_actual < $record->stock_minimo)
                    ->modalHeading('Solicitar Orden de Compra')
                    ->modalDescription(fn ($record) =>
                        "El producto {$record->producto->nombre} está por debajo del stock mínimo. " .
                        "Faltan " . number_format($record->stock_minimo - $record->stock_actual, 2) . " unidades."
                    )
                    ->url(fn ($record): string =>
                        route('filament.admin.resources.pedido-compras.create', [
                            'producto_id'        => $record->producto_id,
                            'cantidad_necesaria'  => ceil($record->stock_minimo - $record->stock_actual),
                            'producto_nombre'     => $record->producto->nombre,
                            'proveedor_id'        => $record->producto?->proveedor_id ?? null,
                        ])
                    ),

                Tables\Actions\Action::make('ver_movimientos')
                    ->label('Ver Movimientos')
                    ->icon('heroicon-m-chart-bar')->color('info')
                    ->url(fn ($record) =>
                        route('filament.admin.resources.movimiento-inventarios.index', [
                            'tableFilters[producto_id][value]' => $record->producto_id,
                            'tableFilters[almacen_id][value]'  => $record->almacen_id,
                        ])
                    ),
            ])
            ->headerActions([
                Tables\Actions\Action::make('exportar_general')
                    ->label('Exportar Inventario General')
                    ->icon('heroicon-m-document-arrow-down')->color('success')
                    ->action(fn () => Excel::download(new InventarioGeneralExport(), 'inventario_general_' . date('Y-m-d') . '.xlsx')),

                Tables\Actions\Action::make('exportar_por_sucursal')
                    ->label('Exportar por Sucursal')
                    ->icon('heroicon-m-document-arrow-down')->color('info')
                    ->action(fn () => Excel::download(new InventarioSucursalExport(), 'inventario_por_sucursal_' . date('Y-m-d') . '.xlsx')),

                Tables\Actions\Action::make('limpiar_filtros')
                    ->label('Limpiar Filtros')
                    ->icon('heroicon-m-x-mark')->color('gray')
                    ->action(function ($livewire) {
                        $livewire->resetTableFilters();
                        \Filament\Notifications\Notification::make()
                            ->title('Filtros limpiados')
                            ->body('Se han eliminado todos los filtros aplicados')
                            ->success()->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    BulkAction::make('actualizar_stock_minimo')
                        ->label('Actualizar Stock Mínimo')
                        ->icon('heroicon-m-pencil')
                        ->form([
                            Forms\Components\TextInput::make('stock_minimo')
                                ->label('Nuevo Stock Mínimo')->numeric()->required()->step(0.001),
                            Forms\Components\Select::make('aplicar_a')
                                ->label('Aplicar a')
                                ->options([
                                    'seleccionados'  => 'Solo productos seleccionados',
                                    'todos_producto' => 'Todas las sucursales del mismo producto',
                                    'todos_sucursal' => 'Todos los productos de la misma sucursal',
                                ])
                                ->default('seleccionados'),
                        ])
                        ->action(function (Collection $records, array $data) {
                            foreach ($records as $record) {
                                if ($data['aplicar_a'] === 'todos_producto') {
                                    InventarioAlmacen::where('producto_id', $record->producto_id)
                                        ->update(['stock_minimo' => $data['stock_minimo']]);
                                } elseif ($data['aplicar_a'] === 'todos_sucursal') {
                                    InventarioAlmacen::where('almacen_id', $record->almacen_id)
                                        ->update(['stock_minimo' => $data['stock_minimo']]);
                                } else {
                                    $record->update(['stock_minimo' => $data['stock_minimo']]);
                                }
                            }
                            \Filament\Notifications\Notification::make()
                                ->title('Stock mínimo actualizado')->success()->send();
                        }),

                    BulkAction::make('exportar_seleccion')
                        ->label('Exportar Seleccionados')
                        ->icon('heroicon-m-document-arrow-down')->color('success')
                        ->action(fn (Collection $records) =>
                            Excel::download(new InventarioGeneralExport($records), 'inventario_seleccionado_' . date('Y-m-d') . '.xlsx')
                        ),
                ]),
            ])
            ->defaultSort('almacen_id', 'asc')
            ->groups([
                Tables\Grouping\Group::make('almacen.nombre')
                    ->label('Agrupar por Sucursal')->collapsible(),
                Tables\Grouping\Group::make('producto.categoria.nombre')
                    ->label('Agrupar por Categoría')->collapsible(),
                Tables\Grouping\Group::make('estado')
                    ->label('Agrupar por Estado')->collapsible(),
            ])
            ->persistFiltersInSession()
            ->persistSearchInSession();
    }

    protected static function getTrasladoForm($record): array
    {
        $sucursalesDisponibles = Almacen::where('id', '!=', $record->almacen_id)
            ->where('activo', true)->get();

        $opcionesSucursales = [];
        foreach ($sucursalesDisponibles as $sucursal) {
            $inventarioDestino = InventarioAlmacen::where('producto_id', $record->producto_id)
                ->where('almacen_id', $sucursal->id)->first();

            $stockActualDestino  = $inventarioDestino?->stock_actual ?? 0;
            $stockMaximoDestino  = $inventarioDestino?->stock_maximo ?? $record->stock_maximo;
            $capacidadDisponible = max(0, $stockMaximoDestino - $stockActualDestino);

            if ($capacidadDisponible > 0) {
                $porcentajeOcupacion = $stockMaximoDestino > 0 ? ($stockActualDestino / $stockMaximoDestino) * 100 : 0;
                $opcionesSucursales[$sucursal->id] = sprintf(
                    "%s Capacidad: %.0f%% (disp: %.2f) | Actual: %.2f / Máx: %.2f",
                    $sucursal->nombre, $porcentajeOcupacion, $capacidadDisponible, $stockActualDestino, $stockMaximoDestino
                );
            }
        }

        $cantidadMaximaSugerida = min(
            $record->stock_actual - $record->stock_maximo,
            !empty($opcionesSucursales) ? max(array_keys($opcionesSucursales)) : 0
        );

        return [
            Forms\Components\Select::make('sucursal_destino_id')
                ->label('Sucursal Destino')
                ->options($opcionesSucursales)->required()->searchable()
                ->placeholder('Seleccione una sucursal')
                ->helperText('Solo se muestran sucursales con capacidad disponible')
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set, $get, $record) {
                    if ($state) {
                        $inventarioDestino = InventarioAlmacen::where('producto_id', $record->producto_id)
                            ->where('almacen_id', $state)->first();
                        $capacidadDisponible = ($inventarioDestino?->stock_maximo ?? $record->stock_maximo)
                            - ($inventarioDestino?->stock_actual ?? 0);
                        $set('capacidad_disponible', $capacidadDisponible);
                    }
                }),

            Forms\Components\Placeholder::make('capacidad_disponible')
                ->label('Capacidad disponible en destino')
                ->content(fn ($get) => $get('sucursal_destino_id') ? 'Calculando...' : 'Seleccione una sucursal')
                ->visible(fn ($get) => !empty($get('sucursal_destino_id'))),

            Forms\Components\TextInput::make('cantidad')
                ->label('Cantidad a trasladar')->numeric()
                ->default(max(1, $cantidadMaximaSugerida))
                ->minValue(1)->maxValue($record->stock_actual - $record->stock_maximo)->required()
                ->helperText(fn () => "Máximo disponible: " . number_format(max(0, $record->stock_actual - $record->stock_maximo), 2)),

            Forms\Components\Textarea::make('motivo')
                ->label('Motivo del traslado')
                ->default('Reubicación por excedente de inventario')
                ->rows(3)->required(),
        ];
    }

    protected static function procesarTraslado($record, array $data): void
    {
        try {
            $sucursalDestino = Almacen::find($data['sucursal_destino_id']);
            $cantidad        = $data['cantidad'];
            $excedente       = $record->stock_actual - $record->stock_maximo;

            if ($cantidad > $excedente) {
                \Filament\Notifications\Notification::make()
                    ->title('Error')
                    ->body("No puedes trasladar más de {$excedente} unidades (excedente actual)")
                    ->danger()->send();
                return;
            }

            $inventarioDestino   = InventarioAlmacen::where('producto_id', $record->producto_id)
                ->where('almacen_id', $sucursalDestino->id)->first();
            $capacidadDisponible = ($inventarioDestino?->stock_maximo ?? $record->stock_maximo)
                - ($inventarioDestino?->stock_actual ?? 0);

            if ($cantidad > $capacidadDisponible) {
                \Filament\Notifications\Notification::make()
                    ->title('Error')
                    ->body("La sucursal destino solo tiene capacidad para {$capacidadDisponible} unidades")
                    ->danger()->send();
                return;
            }

            $record->stock_actual -= $cantidad;
            $record->save();

            if ($inventarioDestino) {
                $inventarioDestino->stock_actual += $cantidad;
                $inventarioDestino->save();
            } else {
                InventarioAlmacen::create([
                    'producto_id'   => $record->producto_id,
                    'almacen_id'    => $sucursalDestino->id,
                    'stock_actual'  => $cantidad,
                    'stock_minimo'  => $record->stock_minimo,
                    'stock_maximo'  => $record->stock_maximo,
                    'punto_reorden' => $record->stock_minimo * 0.8,
                ]);
            }

            \Filament\Notifications\Notification::make()
                ->title('Traslado completado')
                ->body("Se han trasladado {$cantidad} unidades de {$record->producto->nombre} a {$sucursalDestino->nombre}")
                ->success()->send();

        } catch (\Exception $e) {
            \Filament\Notifications\Notification::make()
                ->title('Error al procesar traslado')->body($e->getMessage())->danger()->send();
        }
    }
}
