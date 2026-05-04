<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventarioAlmacenResource\Pages;
use App\Models\InventarioAlmacen;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;

class InventarioAlmacenResource extends Resource
{
    protected static ?string $model = InventarioAlmacen::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel = 'Inventario por Sucursal';
    protected static ?string $navigationGroup = 'Inventario';
    protected static ?int $navigationSort = 2;
    protected static ?string $modelLabel = 'Inventario';
    protected static ?string $pluralModelLabel = 'Inventario por Sucursal';

    public static function table(Table $table): Table
    {
        return $table
            ->query(InventarioAlmacen::query()->with(['producto', 'almacen']))
            ->columns([
                TextColumn::make('producto.nombre')
                    ->label('Producto')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('almacen.nombre')
                    ->label('Sucursal')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                    
                TextColumn::make('stock_actual')
                    ->label('Stock Actual')
                    ->numeric(3)
                    ->sortable()
                    ->color(fn($record) => match(true) {
                        $record->stock_actual >= $record->stock_maximo => 'warning',
                        $record->stock_actual <= $record->stock_minimo => 'danger',
                        default => 'success',
                    }),
                    
                TextColumn::make('stock_minimo')
                    ->label('Mínimo')
                    ->numeric(3)
                    ->sortable(),
                    
                TextColumn::make('stock_maximo')
                    ->label('Máximo')
                    ->numeric(3)
                    ->sortable(),
                    
                TextColumn::make('diferencia')
                    ->label('Diferencia')
                    ->getStateUsing(fn($record) => match(true) {
                        $record->stock_actual > $record->stock_maximo => 
                            '+' . number_format($record->stock_actual - $record->stock_maximo, 2) . ' (sobra)',
                        $record->stock_actual < $record->stock_minimo => 
                            '-' . number_format($record->stock_minimo - $record->stock_actual, 2) . ' (falta)',
                        default => '✓ OK',
                    })
                    ->badge()
                    ->color(fn($record) => match(true) {
                        $record->stock_actual > $record->stock_maximo => 'warning',
                        $record->stock_actual < $record->stock_minimo => 'danger',
                        default => 'success',
                    }),
                    
                BadgeColumn::make('estado')
                    ->label('Estado')
                    ->getStateUsing(fn($record) => match(true) {
                        $record->stock_actual >= $record->stock_maximo => 'Sobrestock',
                        $record->stock_actual <= $record->stock_minimo => 'Stock Bajo',
                        default => 'Óptimo',
                    })
                    ->colors([
                        'warning' => 'Sobrestock',
                        'danger' => 'Stock Bajo',
                        'success' => 'Óptimo',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('almacen_id')
                    ->label('Sucursal')
                    ->relationship('almacen', 'nombre')
                    ->multiple(),
                    
                Tables\Filters\SelectFilter::make('estado')
                    ->label('Estado de Stock')
                    ->options([
                        'sobrestock' => 'Sobrestock',
                        'bajo' => 'Stock Bajo',
                        'optimo' => 'Óptimo',
                    ])
                    ->query(function ($query, $data) {
                        if ($data['value'] === 'sobrestock') {
                            $query->whereColumn('stock_actual', '>=', 'stock_maximo');
                        } elseif ($data['value'] === 'bajo') {
                            $query->whereColumn('stock_actual', '<=', 'stock_minimo');
                        } elseif ($data['value'] === 'optimo') {
                            $query->whereColumn('stock_actual', '>', 'stock_minimo')
                                  ->whereColumn('stock_actual', '<', 'stock_maximo');
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('rebalancear')
                ->label('Sugerir Traslado')
                ->icon('heroicon-m-arrow-path')
                ->color('warning')
                ->visible(fn($record) => $record->stock_actual > $record->stock_maximo)
                ->url(fn($record): string => 
                    route('filament.admin.resources.traslados.create', [
                        'producto_id' => $record->producto_id,
                        'origen_id' => $record->almacen_id,
                        'nombre_producto' => $record->producto->nombre,
                        'origen_nombre' => $record->almacen->nombre,
                        'stock_actual' => $record->stock_actual,
                        'stock_maximo' => $record->stock_maximo,
                        'excedente' => $record->stock_actual - $record->stock_maximo
                    ])
                ), 
                
                Tables\Actions\Action::make('solicitar')
                    ->label('Solicitar Compra')
                    ->icon('heroicon-m-shopping-cart')
                    ->color('danger')
                    ->visible(fn($record) => $record->stock_actual < $record->stock_minimo)
                    ->url(fn($record): string => 
                        route('filament.admin.resources.pedido-compras.create', [
                            'producto_id' => $record->producto_id,
                            'cantidad_necesaria' => ceil($record->stock_minimo - $record->stock_actual),
                            'producto_nombre' => $record->producto->nombre,
                            'proveedor_id' => $record->producto->proveedor_id ?? null
                        ])
                    ),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventarioAlmacens::route('/'),
        ];
    }

    public static function getTrasladoForm($record): array
{
    // Obtener todas las sucursales excepto la actual
    $sucursalesDisponibles = \App\Models\Almacen::where('id', '!=', $record->almacen_id)
        ->where('activo', true)
        ->get();
    
    // Calcular capacidad disponible por sucursal
    $opcionesSucursales = [];
    foreach ($sucursalesDisponibles as $sucursal) {
        // Obtener inventario actual de la sucursal destino
        $inventarioDestino = \App\Models\InventarioAlmacen::where('producto_id', $record->producto_id)
            ->where('almacen_id', $sucursal->id)
            ->first();
        
        $stockActualDestino = $inventarioDestino ? $inventarioDestino->stock_actual : 0;
        $stockMaximoDestino = $inventarioDestino ? $inventarioDestino->stock_maximo : $record->stock_maximo;
        
        $capacidadDisponible = max(0, $stockMaximoDestino - $stockActualDestino);
        $excedenteOrigen = $record->stock_actual - $record->stock_maximo;
        
        if ($capacidadDisponible > 0) {
            $opcionesSucursales[$sucursal->id] = sprintf(
                "%s - Capacidad disponible: %.2f (Actual: %.2f / Máx: %.2f)",
                $sucursal->nombre,
                $capacidadDisponible,
                $stockActualDestino,
                $stockMaximoDestino
            );
        }
    }
    
    $cantidadMaximaSugerida = min(
        $record->stock_actual - $record->stock_maximo, // Excedente en origen
        max($opcionesSucursales ? array_values($opcionesSucursales) : [0]) // Capacidad máxima disponible
    );
    
    return [
        \Filament\Forms\Components\Select::make('sucursal_destino_id')
            ->label('Sucursal Destino')
            ->options($opcionesSucursales)
            ->required()
            ->searchable()
            ->placeholder('Seleccione una sucursal')
            ->helperText('Solo se muestran sucursales con capacidad disponible'),
        
        \Filament\Forms\Components\TextInput::make('cantidad')
            ->label('Cantidad a trasladar')
            ->numeric()
            ->default(min($cantidadMaximaSugerida, $record->stock_maximo * 0.5))
            ->minValue(1)
            ->maxValue($record->stock_actual - $record->stock_maximo)
            ->required()
            ->helperText(fn() => "Máximo disponible para trasladar: " . number_format($record->stock_actual - $record->stock_maximo, 2)),
        
        \Filament\Forms\Components\Textarea::make('motivo')
            ->label('Motivo del traslado')
            ->default('Reubicación por excedente de inventario')
            ->rows(3)
            ->required(),
    ];
}

    public static function procesarTraslado($record, array $data)
    {
        try {
            $sucursalDestino = \App\Models\Almacen::find($data['sucursal_destino_id']);
            $cantidad = $data['cantidad'];
            
            // Verificar que la cantidad no exceda el excedente
            $excedente = $record->stock_actual - $record->stock_maximo;
            if ($cantidad > $excedente) {
                \Filament\Notifications\Notification::make()
                    ->title('Error')
                    ->body("No puedes trasladar más de {$excedente} unidades (excedente actual)")
                    ->danger()
                    ->send();
                return;
            }
            
            // Verificar capacidad en destino
            $inventarioDestino = \App\Models\InventarioAlmacen::where('producto_id', $record->producto_id)
                ->where('almacen_id', $sucursalDestino->id)
                ->first();
            
            $capacidadDisponible = ($inventarioDestino ? $inventarioDestino->stock_maximo : $record->stock_maximo) 
                - ($inventarioDestino ? $inventarioDestino->stock_actual : 0);
            
            if ($cantidad > $capacidadDisponible) {
                \Filament\Notifications\Notification::make()
                    ->title('Error')
                    ->body("La sucursal destino solo tiene capacidad para {$capacidadDisponible} unidades")
                    ->danger()
                    ->send();
                return;
            }
            
            // Crear registro de traslado
            $traslado = \App\Models\Traslado::create([
                'numero' => 'TRAS-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                'producto_id' => $record->producto_id,
                'almacen_origen_id' => $record->almacen_id,
                'almacen_destino_id' => $sucursalDestino->id,
                'cantidad_sugerida' => $cantidad,
                'cantidad_real' => $cantidad,
                'estado' => 'aprobado',
                'motivo' => $data['motivo'],
                'creado_por' => auth()->id(),
                'aprobado_por' => auth()->id(),
                'fecha_aprobacion' => now(),
            ]);
            
            // Registrar movimiento de salida en origen
            \App\Models\MovimientoInventario::create([
                'numero' => 'MOV-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                'producto_id' => $record->producto_id,
                'almacen_id' => $record->almacen_id,
                'user_id' => auth()->id(),
                'tipo' => 'traslado_salida',
                'cantidad' => $cantidad,
                'stock_anterior' => $record->stock_actual,
                'stock_nuevo' => $record->stock_actual - $cantidad,
                'referencia_type' => \App\Models\Traslado::class,
                'referencia_id' => $traslado->id,
                'fecha_movimiento' => now(),
                'motivo' => "Traslado a sucursal: {$sucursalDestino->nombre}",
            ]);
            
            // Actualizar stock en origen
            $record->stock_actual -= $cantidad;
            $record->save();
            
            // Registrar movimiento de entrada en destino
            if ($inventarioDestino) {
                $stockAnteriorDestino = $inventarioDestino->stock_actual;
                $inventarioDestino->stock_actual += $cantidad;
                $inventarioDestino->save();
                
                \App\Models\MovimientoInventario::create([
                    'numero' => 'MOV-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                    'producto_id' => $record->producto_id,
                    'almacen_id' => $sucursalDestino->id,
                    'user_id' => auth()->id(),
                    'tipo' => 'traslado_entrada',
                    'cantidad' => $cantidad,
                    'stock_anterior' => $stockAnteriorDestino,
                    'stock_nuevo' => $inventarioDestino->stock_actual,
                    'referencia_type' => \App\Models\Traslado::class,
                    'referencia_id' => $traslado->id,
                    'fecha_movimiento' => now(),
                    'motivo' => "Traslado desde sucursal: {$record->almacen->nombre}",
                ]);
            } else {
                // Crear inventario para la sucursal destino si no existe
                $nuevoInventario = \App\Models\InventarioAlmacen::create([
                    'producto_id' => $record->producto_id,
                    'almacen_id' => $sucursalDestino->id,
                    'stock_actual' => $cantidad,
                    'stock_minimo' => $record->stock_minimo,
                    'stock_maximo' => $record->stock_maximo,
                    'punto_reorden' => $record->stock_minimo * 0.8,
                ]);
                
                \App\Models\MovimientoInventario::create([
                    'numero' => 'MOV-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                    'producto_id' => $record->producto_id,
                    'almacen_id' => $sucursalDestino->id,
                    'user_id' => auth()->id(),
                    'tipo' => 'traslado_entrada',
                    'cantidad' => $cantidad,
                    'stock_anterior' => 0,
                    'stock_nuevo' => $cantidad,
                    'referencia_type' => \App\Models\Traslado::class,
                    'referencia_id' => $traslado->id,
                    'fecha_movimiento' => now(),
                    'motivo' => "Traslado desde sucursal: {$record->almacen->nombre}",
                ]);
            }
            
            \Filament\Notifications\Notification::make()
                ->title('Traslado completado')
                ->body("Se han trasladado {$cantidad} unidades de {$record->producto->nombre} a {$sucursalDestino->nombre}")
                ->success()
                ->send();
                
        } catch (\Exception $e) {
            \Filament\Notifications\Notification::make()
                ->title('Error al procesar traslado')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

}