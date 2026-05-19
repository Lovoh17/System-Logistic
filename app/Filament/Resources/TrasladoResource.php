<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TrasladoResource\Pages;
use App\Models\Traslado;
use App\Models\Almacen;
use App\Models\Transportista;
use App\Models\InventarioAlmacen;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class TrasladoResource extends Resource
{
    protected static ?string $model = Traslado::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';
    protected static ?string $navigationLabel = 'Traslados';
    protected static ?string $navigationGroup = 'Logística';
    protected static ?int $navigationSort = 2;
    protected static ?string $modelLabel = 'Traslado';
    protected static ?string $pluralModelLabel = 'Traslados';

    public static function form(Form $form): Form
    {
        // Obtener parámetros de la URL
        $productoId = request()->query('producto_id');
        $origenId = request()->query('origen_id');
        $excedente = request()->query('excedente');
        $nombreProducto = request()->query('nombre_producto');
        $origenNombre = request()->query('origen_nombre');
        $stockActual = request()->query('stock_actual');
        $stockMaximo = request()->query('stock_maximo');
        
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Traslado')
                    ->columns(4)
                    ->schema([
                        Forms\Components\TextInput::make('numero')
                            ->label('N° Traslado')
                            ->default(fn() => Traslado::generarNumero())
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->columnSpan(1),

                        Forms\Components\Select::make('producto_id')
                            ->label('Producto')
                            ->options(\App\Models\Producto::activo()->pluck('nombre', 'id'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->default($productoId)
                            ->disabled(fn() => !is_null($productoId))
                            ->helperText(fn() => $nombreProducto ? "Producto: {$nombreProducto}" : null)
                            ->columnSpan(2),

                        Forms\Components\Select::make('almacen_origen_id')
                            ->label('Sucursal Origen')
                            ->options(\App\Models\Almacen::where('activo', true)->pluck('nombre', 'id'))
                            ->required()
                            ->default($origenId)
                            ->disabled(fn() => !is_null($origenId))
                            ->helperText(fn() => $origenNombre ? "Origen: {$origenNombre}" : null)
                            ->columnSpan(1),

                        Forms\Components\Select::make('almacen_destino_id')
                            ->label('Sucursal Destino')
                            ->options(\App\Models\Almacen::where('activo', true)
                                ->where('id', '!=', $origenId)
                                ->pluck('nombre', 'id'))
                            ->required()
                            ->searchable()
                            ->columnSpan(1),
                    ]),

                Forms\Components\Section::make('Información de Stock y Capacidad')
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('stock_actual_info')
                            ->label('Stock Actual en Origen')
                            ->default(number_format($stockActual ?? 0, 2))
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('stock_maximo_info')
                            ->label('Stock Máximo Permitido')
                            ->default(number_format($stockMaximo ?? 0, 2))
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('excedente_info')
                            ->label('Excedente (Sobra)')
                            ->default(number_format($excedente ?? 0, 2))
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpan(1),
                    ]),

                Forms\Components\Section::make('Cantidades')
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('cantidad')
                            ->label('Cantidad a Trasladar')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue($excedente ?? 999999)
                            ->default($excedente ? min($excedente, 10) : 1)
                            ->helperText(fn() => $excedente ? "Máximo disponible para trasladar: " . number_format($excedente, 2) : null)
                            ->columnSpan(1),

                        Forms\Components\Select::make('transportista_id')
                            ->label('Transportista (Opcional)')
                            ->options(\App\Models\Transportista::where('estado', 'disponible')->pluck('nombre', 'id'))
                            ->searchable()
                            ->preload()
                            ->columnSpan(2),

                        Forms\Components\DatePicker::make('fecha_programada')
                            ->label('Fecha Programada')
                            ->default(now()->addDays(1))
                            ->required()
                            ->columnSpan(1),

                        Forms\Components\DatePicker::make('fecha_entrega_estimada')
                            ->label('Fecha Estimada de Entrega')
                            ->default(now()->addDays(2))
                            ->columnSpan(1),
                    ]),

                Forms\Components\Section::make('Información Adicional')
                    ->schema([
                        Forms\Components\Textarea::make('motivo')
                            ->label('Motivo del Traslado')
                            ->default('Reubicación por excedente de inventario')
                            ->rows(2)
                            ->required(),
                        Forms\Components\Textarea::make('observaciones')
                            ->label('Observaciones')
                            ->rows(2),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero')
                    ->label('N° Traslado')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('producto.nombre')
                    ->label('Producto')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('almacenOrigen.nombre')
                    ->label('Origen')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('almacenDestino.nombre')
                    ->label('Destino')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('cantidad')
                    ->label('Cantidad')
                    ->numeric(3)
                    ->sortable(),

                Tables\Columns\TextColumn::make('transportista.nombre')
                    ->label('Transportista')
                    ->searchable(),

                Tables\Columns\TextColumn::make('fecha_programada')
                    ->label('Fecha Prog.')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('estado')
                    ->label('Estado')
                    ->colors([
                        'warning' => 'pendiente',
                        'info' => 'asignado',
                        'primary' => 'en_transito',
                        'success' => 'entregado',
                        'danger' => 'cancelado',
                    ])
                    ->icons([
                        'heroicon-m-clock' => 'pendiente',
                        'heroicon-m-truck' => 'en_transito',
                        'heroicon-m-check-circle' => 'entregado',
                        'heroicon-m-x-circle' => 'cancelado',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'asignado' => 'Asignado',
                        'en_transito' => 'En Tránsito',
                        'entregado' => 'Entregado',
                        'cancelado' => 'Cancelado',
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
                    ->icon('heroicon-m-truck')
                    ->color('info')
                    ->visible(fn($record) => $record->estado === 'pendiente')
                    ->form([
                        Forms\Components\Select::make('transportista_id')
                            ->label('Transportista')
                            ->options(Transportista::where('estado', 'disponible')->pluck('nombre', 'id'))
                            ->required(),
                        Forms\Components\DatePicker::make('fecha_salida')
                            ->label('Fecha de Salida')
                            ->default(now()),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'transportista_id' => $data['transportista_id'],
                            'fecha_salida' => $data['fecha_salida'],
                            'estado' => 'asignado',
                            'asignado_por' => auth()->id(),
                        ]);
                        
                        Notification::make()
                            ->title('Transporte asignado')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('iniciar_transito')
                    ->label('Iniciar Tránsito')
                    ->icon('heroicon-m-play')
                    ->color('primary')
                    ->visible(fn($record) => $record->estado === 'asignado')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update(['estado' => 'en_transito']);
                        
                        Notification::make()
                            ->title('Traslado en tránsito')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('completar_entrega')
                    ->label('Completar Entrega')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->estado === 'en_transito')
                    ->form([
                        Forms\Components\DatePicker::make('fecha_entrega_real')
                            ->label('Fecha de Entrega')
                            ->default(now())
                            ->required(),
                        Forms\Components\TextInput::make('cantidad_recibida')
                            ->label('Cantidad Recibida')
                            ->numeric()
                            ->default(fn($record) => $record->cantidad)
                            ->required()
                            ->minValue(0)
                            ->maxValue(fn($record) => $record->cantidad),
                    ])
                    ->action(function ($record, array $data) {
                        // Registrar movimiento de inventario en destino
                        $inventarioDestino = InventarioAlmacen::where('producto_id', $record->producto_id)
                            ->where('almacen_id', $record->almacen_destino_id)
                            ->first();
                        
                        if ($inventarioDestino) {
                            $inventarioDestino->stock_actual += $data['cantidad_recibida'];
                            $inventarioDestino->save();
                        } else {
                            InventarioAlmacen::create([
                                'producto_id' => $record->producto_id,
                                'almacen_id' => $record->almacen_destino_id,
                                'stock_actual' => $data['cantidad_recibida'],
                                'stock_minimo' => 0,
                                'stock_maximo' => 999999,
                            ]);
                        }
                        
                        $record->update([
                            'estado' => 'entregado',
                            'fecha_entrega_real' => $data['fecha_entrega_real'],
                            'cantidad_recibida' => $data['cantidad_recibida'],
                        ]);
                        
                        Notification::make()
                            ->title('Entrega completada')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTraslados::route('/'),
            'create' => Pages\CreateTraslado::route('/create'),
            //'view' => Pages\ViewTraslado::route('/{record}'),
            'edit' => Pages\EditTraslado::route('/{record}/edit'),
        ];
    }
}