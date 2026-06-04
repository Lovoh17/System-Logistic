<?php

namespace App\Filament\Resources\ProductoResource\Pages;

use App\Filament\Resources\ProductoResource;
use App\Models\Almacen;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Exports\InventarioSucursalExport;
use App\Exports\InventarioGeneralExport;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Tables\Table;

class ListProducto extends ListRecords
{
    protected static string $resource = ProductoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['categoria', 'proveedor', 'inventarioAlmacen.almacen']))
            ->columns([
                Tables\Columns\ImageColumn::make('imagen')
                    ->label('')->circular()
                    ->defaultImageUrl(url('/images/no-product.png'))->size(40),

                Tables\Columns\TextColumn::make('codigo')
                    ->label('Código')->searchable()->sortable()
                    ->badge()->color('gray'),

                Tables\Columns\TextColumn::make('nombre')
                    ->label('Producto')->searchable()->sortable()
                    ->description(fn ($record) => $record->categoria?->nombre),

                Tables\Columns\TextColumn::make('proveedor.nombre')
                    ->label('Proveedor')->searchable()->toggleable(),

                Tables\Columns\TextColumn::make('precio_venta')
                    ->label('Precio Venta')->money('USD')->sortable(),

                Tables\Columns\TextColumn::make('stock_total')
                    ->label('Stock Total')->alignCenter()->badge()
                    ->color(fn ($record) => $record->stock_color)
                    ->formatStateUsing(fn ($record) => $record->stock_total . ' ' . $record->unidad_medida),

                Tables\Columns\TextColumn::make('stock_por_sucursal')
                    ->label('Stock por Sucursal')
                    ->formatStateUsing(function ($record) {
                        $stocks = $record->inventarioAlmacen
                            ->map(fn ($inv) => "{$inv->almacen->nombre}: {$inv->stock_actual} {$record->unidad_medida}")
                            ->implode(', ');
                        return $stocks ?: 'Sin stock configurado';
                    })
                    ->html()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('requiere_refrigeracion')
                    ->label('❄️')->boolean()->toggleable(),

                Tables\Columns\TextColumn::make('estado')
                    ->badge()
                    ->colors([
                        'success' => 'activo',
                        'gray'    => 'inactivo',
                        'danger'  => 'descontinuado',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->options([
                        'activo'        => 'Activo',
                        'inactivo'      => 'Inactivo',
                        'descontinuado' => 'Descontinuado',
                    ]),
                Tables\Filters\Filter::make('stock_bajo')
                    ->label('Stock Bajo (alguna sucursal)')
                    ->query(fn (Builder $query) => $query->whereHas('inventarioAlmacen', function ($q) {
                        $q->whereColumn('stock_actual', '<=', 'stock_minimo');
                    })),
                Tables\Filters\Filter::make('sin_stock')
                    ->label('Sin Stock (todas las sucursales)')
                    ->query(fn (Builder $query) => $query->whereDoesntHave('inventarioAlmacen', function ($q) {
                        $q->where('stock_actual', '>', 0);
                    })),
                Tables\Filters\SelectFilter::make('categoria_id')
                    ->label('Categoría')->relationship('categoria', 'nombre'),
                Tables\Filters\SelectFilter::make('proveedor_id')
                    ->label('Proveedor')->relationship('proveedor', 'nombre'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('actualizar_stock_minimo')
                        ->label('Actualizar Stock Mínimo')
                        ->icon('heroicon-m-pencil')
                        ->form([
                            Forms\Components\TextInput::make('stock_minimo')
                                ->label('Nuevo Stock Mínimo')->numeric()->required()->step(0.001),
                            Forms\Components\Select::make('almacen_id')
                                ->label('Aplicar a sucursal')
                                ->options(Almacen::where('activo', true)->pluck('nombre', 'id'))
                                ->placeholder('Todas las sucursales'),
                        ])
                        ->action(function (Collection $records, array $data) {
                            foreach ($records as $record) {
                                $query = $record->inventarioAlmacen();
                                if (isset($data['almacen_id']) && $data['almacen_id']) {
                                    $query->where('almacen_id', $data['almacen_id']);
                                }
                                $query->update(['stock_minimo' => $data['stock_minimo']]);
                            }
                        }),
                ]),
            ])
            ->defaultSort('codigo', 'asc');
    }
}
