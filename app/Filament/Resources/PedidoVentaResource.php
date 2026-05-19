<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PedidoVentaResource\Pages;
use App\Models\PedidoVenta;
use App\Models\Producto;
use App\Models\Cliente;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class PedidoVentaResource extends Resource
{
    protected static ?string $model = PedidoVenta::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Pedidos de Venta';
    protected static ?string $navigationGroup = 'Pedidos';
    protected static ?int $navigationSort = 2;
    protected static ?string $modelLabel = 'Pedido de Venta';
    protected static ?string $pluralModelLabel = 'Pedidos de Venta';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Encabezado del Pedido')
                ->icon('heroicon-o-document-text')
                ->columns(4)
                ->schema([
                    Forms\Components\TextInput::make('numero')
                        ->label('N° Pedido')
                        ->default(fn() => PedidoVenta::generarNumero())
                        ->disabled()
                        ->dehydrated()
                        ->required()
                        ->columnSpan(1),

                    Forms\Components\Select::make('cliente_id')
                        ->label('Cliente')
                        ->relationship('cliente', 'nombre')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('nombre')->required(),
                            Forms\Components\TextInput::make('telefono'),
                            Forms\Components\TextInput::make('email')->email(),
                        ])
                        ->columnSpan(2),

                    Forms\Components\Select::make('estado')
                        ->options([
                            'borrador'       => 'Borrador',
                            'confirmado'     => 'Confirmado',
                            'en_preparacion' => 'En Preparación',
                            'listo'          => 'Listo para Despacho',
                            'en_transito'    => 'En Tránsito',
                            'entregado'      => 'Entregado',
                            'cancelado'      => 'Cancelado',
                            'devolucion'     => 'Devolución',
                        ])
                        ->default('borrador')
                        ->required()
                        ->columnSpan(1),

                    Forms\Components\DatePicker::make('fecha_pedido')
                        ->label('Fecha del Pedido')
                        ->default(now())
                        ->required()
                        ->columnSpan(1),

                    Forms\Components\DatePicker::make('fecha_requerida')
                        ->label('Fecha Requerida de Entrega')
                        ->minDate(now())
                        ->columnSpan(1),

                    Forms\Components\Select::make('prioridad')
                        ->options([
                            'baja'    => 'Baja',
                            'normal'  => 'Normal',
                            'alta'    => 'Alta',
                            'urgente' => '🔴 Urgente',
                        ])
                        ->default('normal')
                        ->required()
                        ->columnSpan(1),

                    Forms\Components\Select::make('canal_venta')
                        ->label('Canal de Venta')
                        ->options([
                            'directo'      => 'Directo',
                            'telefono'     => 'Teléfono',
                            'web'          => 'Sitio Web',
                            'distribuidor' => 'Distribuidor',
                            'whatsapp'     => 'WhatsApp',
                        ])
                        ->default('directo')
                        ->columnSpan(1),

                    Forms\Components\Select::make('almacen_id')
                        ->label('Sucursal')
                        ->relationship('almacen', 'nombre')
                        ->searchable()
                        ->preload()
                        ->default(fn() => auth()->user()->almacen_id)
                        ->disabled(fn() => auth()->user()->rol !== 'super-admin')
                        ->columnSpan(1),
                ]),

            Forms\Components\Section::make('Dirección de Entrega')
                ->icon('heroicon-o-map-pin')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('departamento_entrega')
                        ->label('Departamento')
                        ->maxLength(80)
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('municipio_entrega')
                        ->label('Municipio')
                        ->maxLength(80)
                        ->columnSpan(1),

                    Forms\Components\Textarea::make('direccion_entrega')
                        ->label('Dirección Completa')
                        ->rows(2)
                        ->columnSpan(1),

                    Forms\Components\Textarea::make('instrucciones_entrega')
                        ->label('Instrucciones de Entrega')
                        ->rows(2)
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Líneas de Pedido')
                ->icon('heroicon-o-list-bullet')
                ->schema([
                    Forms\Components\Repeater::make('items')
    ->relationship()
    ->label('')
    ->live()
    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
        $subtotal = 0;
        foreach ($state as $item) {
            $subtotal += $item['subtotal'] ?? 0;
        }
        $set('subtotal', $subtotal);
        $impuesto = round($subtotal * 0.13, 2);
        $set('impuesto', $impuesto);
        $costo_envio = (float)($get('costo_envio') ?? 0);
        $set('total', round($subtotal + $impuesto + $costo_envio, 2));
    })
    ->columns(6)
    ->schema([
        Forms\Components\Select::make('producto_id')
            ->label('Producto')
            ->options(Producto::activo()->pluck('nombre', 'id'))
            ->searchable()
            ->required()
            ->live()
            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                $producto = Producto::find($state);
                if ($producto) {
                    $set('precio_unitario', $producto->precio_venta);
                    $set('unidad_medida', $producto->unidad_medida);
                    // Calcular subtotal inmediatamente
                    $cantidad = (float)($get('cantidad') ?? 1);
                    $descuento = (float)($get('descuento') ?? 0);
                    $set('subtotal', round($cantidad * $producto->precio_venta * (1 - $descuento / 100), 2));
                }
            })
            ->columnSpan(2),

        Forms\Components\TextInput::make('cantidad')
            ->label('Cantidad')
            ->numeric()
            ->default(1)
            ->minValue(0.001)
            ->step(0.001)
            ->required()
            ->live()
            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                $precio = (float)($get('precio_unitario') ?? 0);
                $descuento = (float)($get('descuento') ?? 0);
                $set('subtotal', round((float)$state * $precio * (1 - $descuento / 100), 2));
            })
            ->columnSpan(1),

        Forms\Components\TextInput::make('precio_unitario')
            ->label('Precio Unit.')
            ->numeric()
            ->prefix('$')
            ->required()
            ->live()
            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                $cantidad = (float)($get('cantidad') ?? 1);
                $descuento = (float)($get('descuento') ?? 0);
                $set('subtotal', round($cantidad * (float)$state * (1 - $descuento / 100), 2));
            })
            ->columnSpan(1),

        Forms\Components\TextInput::make('descuento')
            ->label('Desc. %')
            ->numeric()
            ->default(0)
            ->minValue(0)
            ->maxValue(100)
            ->suffix('%')
            ->live()
            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                $cantidad = (float)($get('cantidad') ?? 1);
                $precio = (float)($get('precio_unitario') ?? 0);
                $set('subtotal', round($cantidad * $precio * (1 - (float)$state / 100), 2));
            })
            ->columnSpan(1),

        Forms\Components\TextInput::make('subtotal')
            ->label('Subtotal')
            ->numeric()
            ->prefix('$')
            ->disabled()
            ->dehydrated() 
            ->default(0)
            ->columnSpan(1),
    ])
                ]),

            Forms\Components\Section::make('Totales')
                ->icon('heroicon-o-calculator')
                ->columns(4)
                ->schema([
                    Forms\Components\TextInput::make('subtotal')
                        ->label('Subtotal')
                        ->numeric()
                        ->prefix('$')
                        ->default(0)
                        ->disabled()
                        ->dehydrated()
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('impuesto')
                        ->label('IVA (13%)')
                        ->numeric()
                        ->prefix('$')
                        ->default(0)
                        ->disabled()
                        ->dehydrated()
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('costo_envio')
    ->label('Costo de Envío')
    ->numeric()
    ->prefix('$')
    ->default(0)
    ->live()
    ->afterStateUpdated(function ($state, callable $set, callable $get) {
        $subtotal = $get('subtotal') ?? 0;
        $impuesto = $subtotal * 0.13;
        $total = $subtotal + $impuesto + ($state ?? 0);
        $set('total', $total);
    })
    ->columnSpan(1),

                    Forms\Components\TextInput::make('total')
                        ->label('TOTAL')
                        ->numeric()
                        ->prefix('$')
                        ->default(0)
                        ->disabled()
                        ->dehydrated()
                        ->columnSpan(1),

                    Forms\Components\Textarea::make('notas')
                        ->label('Observaciones')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ]); 
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero')
                    ->label('N° Pedido')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('cliente.nombre')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('fecha_pedido')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('fecha_requerida')
                    ->label('Requerido')
                    ->date('d/m/Y')
                    ->sortable()
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
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->toggleable(),

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
                    ->money('USD')
                    ->sortable()
                    ->alignRight(),

                Tables\Columns\TextColumn::make('canal_venta')
                    ->label('Canal')
                    ->badge()
                    ->color('gray')
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
                            ->when($data['desde'], fn($q, $d) => $q->whereDate('fecha_pedido', '>=', $d))
                            ->when($data['hasta'], fn($q, $d) => $q->whereDate('fecha_pedido', '<=', $d));
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

    public static function getNavigationBadge(): ?string
    {
        $urgentes = static::getModel()::whereIn('estado', ['confirmado', 'en_preparacion'])->count();
        return $urgentes > 0 ? (string) $urgentes : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPedidoVenta::route('/'),
            'create' => Pages\CreatePedidoVenta::route('/create'),
            'view'   => Pages\ViewPedidoVenta::route('/{record}'),
            'edit'   => Pages\EditPedidoVenta::route('/{record}/edit'),
        ];
    }
}
