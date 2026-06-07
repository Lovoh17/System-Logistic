<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PedidoCompraResource\Pages;
use App\Models\Almacen;
use App\Models\InventarioAlmacen;
use App\Models\MovimientoInventario;
use App\Models\PedidoCompra;
use App\Models\PedidoCompraItem;
use App\Models\Producto;
use App\Models\Proveedor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;

class PedidoCompraResource extends Resource
{
    protected static ?string $model = PedidoCompra::class;

    protected static ?string $navigationIcon   = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel  = 'Órdenes de Compra';
    protected static ?string $navigationGroup  = 'Pedidos';
    protected static ?int    $navigationSort   = 1;
    protected static ?string $modelLabel       = 'Orden de Compra';
    protected static ?string $pluralModelLabel = 'Órdenes de Compra';

    public static function form(Form $form): Form
    {
        return $form->schema(self::getFormSchema());
    }

    public static function getFormSchema(): array
    {
        $productoId        = (int)   request()->query('producto_id',        0);
        $cantidadNecesaria = (float) request()->query('cantidad_necesaria', 1);
        $proveedorId       = (int)   request()->query('proveedor_id',       0);
        $fromSession       = (bool)  request()->query('from_session',       0);
        $producto          = $productoId ? Producto::find($productoId) : null;

        // Items desde sesión (cuando viene del botón "Crear OC para proveedor X")
        $sessionItems = [];
        if ($fromSession && session()->has('oc_items_proveedor')) {
            $sessionItems = session()->pull('oc_items_proveedor'); // pull = leer y borrar
        }

        // Default items: sesión > producto individual > vacío
        $defaultItems = match(true) {
            !empty($sessionItems) => $sessionItems,
            $producto !== null    => [[
                'producto_id'     => $producto->id,
                'cantidad'        => max(1, (int) ceil($cantidadNecesaria)),
                'precio_unitario' => (float) $producto->precio_compra,
                'unidad_medida'   => $producto->unidad_medida,
                'subtotal'        => round(
                    max(1, (int) ceil($cantidadNecesaria)) * (float) $producto->precio_compra, 2
                ),
            ]],
            default => [],
        };

        return [
            Forms\Components\Section::make('Encabezado de la Orden de Compra')
                ->icon('heroicon-o-document-text')
                ->columns(4)
                ->schema([
                    Forms\Components\TextInput::make('numero')
                        ->label('N° OC')
                        ->default(fn() => PedidoCompra::generarNumero())
                        ->disabled()->dehydrated()->required()
                        ->columnSpan(1),

                    Forms\Components\Select::make('proveedor_id')
                        ->label('Proveedor')
                        ->relationship('proveedor', 'nombre')
                        ->searchable()->preload()->required()
                        ->default($proveedorId ?: null)
                        ->live()
                        ->afterStateUpdated(function ($state) {
                            if (!$state) return;
                            $prov = Proveedor::find($state);
                            if ($prov && $prov->estado !== 'activo') {
                                Notification::make()
                                    ->warning()
                                    ->title('Proveedor no activo')
                                    ->body("El proveedor \"{$prov->nombre}\" está en estado: {$prov->estado}.")
                                    ->persistent()
                                    ->send();
                            }
                        })
                        ->columnSpan(2),

                    Forms\Components\Select::make('estado')
                        ->options([
                            'borrador'   => 'Borrador',
                            'enviado'    => 'Enviado al Proveedor',
                            'confirmado' => 'Confirmado',
                            'parcial'    => 'Parcialmente Recibido',
                            'recibido'   => 'Completamente Recibido',
                            'cancelado'  => 'Cancelado',
                        ])
                        ->default('borrador')->required()
                        ->columnSpan(1),

                    Forms\Components\DatePicker::make('fecha_pedido')
                        ->label('Fecha del Pedido')
                        ->default(now())->required()->columnSpan(1),

                    Forms\Components\DatePicker::make('fecha_requerida')
                        ->label('Fecha de Entrega Requerida')
                        ->minDate(now())
                        ->default(now()->addDays(7))
                        ->columnSpan(1),

                    Forms\Components\DatePicker::make('fecha_recepcion')
                        ->label('Fecha Real de Recepción')
                        ->live()
                        ->afterStateUpdated(function ($state, Forms\Get $get) {
                            $fechaPedido = $get('fecha_pedido');
                            if ($state && $fechaPedido && $state < $fechaPedido) {
                                Notification::make()->danger()
                                    ->title('Fecha inválida')
                                    ->body('La fecha de recepción no puede ser anterior a la fecha del pedido.')
                                    ->send();
                            }
                        })
                        ->columnSpan(1),

                    Forms\Components\Select::make('moneda')
                        ->options(['USD' => '$ USD', 'EUR' => '€ EUR'])
                        ->default('USD')->columnSpan(1),
                ]),

            Forms\Components\Section::make('Productos a Ordenar')
                ->icon('heroicon-o-list-bullet')
                ->schema([
                    Forms\Components\Repeater::make('items')
                        ->relationship()
                        ->label('')
                        ->columns(7)
                        ->live()
                        ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                            self::calcularTotales($get, $set);
                        })
                        ->defaultItems(0)
                        ->default($defaultItems)
                        ->schema([
                            Forms\Components\Select::make('producto_id')
                                ->label('Producto')
                                ->options(Producto::activo()->pluck('nombre', 'id'))
                                ->searchable()->required()
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                    $p = Producto::find($state);
                                    if ($p) {
                                        $set('precio_unitario', $p->precio_compra);
                                        $set('unidad_medida', $p->unidad_medida);
                                        $set('subtotal', round(
                                            ($get('cantidad') ?? 1) * $p->precio_compra, 2
                                        ));
                                    }
                                    self::calcularTotales($get, $set);
                                })
                                ->columnSpan(2),

                            Forms\Components\TextInput::make('cantidad')
                                ->label('Cantidad')
                                ->numeric()->default(1)->minValue(0.001)->step(0.001)
                                ->required()->live(debounce: 500)
                                ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                    $cantidad  = floatval($state);
                                    $precio    = floatval($get('precio_unitario') ?? 0);
                                    $descuento = floatval($get('descuento') ?? 0);

                                    if ($descuento == 0) {
                                        if ($cantidad >= 100)    $set('descuento', 15);
                                        elseif ($cantidad >= 50) $set('descuento', 10);
                                        elseif ($cantidad >= 10) $set('descuento', 5);
                                    }

                                    $desc = floatval($get('descuento') ?? 0);
                                    $set('subtotal', round($cantidad * $precio * (1 - $desc / 100), 2));
                                    self::calcularTotales($get, $set);
                                })
                                ->columnSpan(1),

                            Forms\Components\TextInput::make('precio_unitario')
                                ->label('Precio Unit.')
                                ->numeric()->prefix('$')->required()->live(debounce: 500)
                                ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                    $set('subtotal', round(
                                        floatval($get('cantidad') ?? 0) * floatval($state) * (1 - floatval($get('descuento') ?? 0) / 100), 2
                                    ));
                                    self::calcularTotales($get, $set);
                                })
                                ->columnSpan(1),

                            Forms\Components\TextInput::make('descuento')
                                ->label('Desc. %')
                                ->numeric()->default(0)->minValue(0)->maxValue(100)->suffix('%')
                                ->live(debounce: 500)
                                ->helperText('≥10u: 5% · ≥50u: 10% · ≥100u: 15%')
                                ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                    $set('subtotal', round(
                                        floatval($get('cantidad') ?? 0) * floatval($get('precio_unitario') ?? 0) * (1 - floatval($state) / 100), 2
                                    ));
                                    self::calcularTotales($get, $set);
                                })
                                ->columnSpan(1),

                            Forms\Components\TextInput::make('subtotal')
                                ->label('Subtotal')
                                ->numeric()->prefix('$')->disabled()->dehydrated()
                                ->columnSpan(1),

                            Forms\Components\TextInput::make('unidad_medida')
                                ->label('Unidad')
                                ->disabled()->dehydrated()
                                ->columnSpan(1),
                        ])
                        ->addActionLabel('+ Agregar Producto')
                        ->reorderable()->collapsible()
                        ->itemLabel(fn(array $state): ?string =>
                        isset($state['producto_id'])
                            ? (Producto::find($state['producto_id'])?->nombre ?? 'Producto')
                            : null
                        ),
                ]),

            Forms\Components\Section::make('Totales y Condiciones')
                ->icon('heroicon-o-calculator')
                ->columns(4)
                ->schema([
                    Forms\Components\TextInput::make('subtotal')
                        ->label('Subtotal')
                        ->numeric()->prefix('$')->default(0)
                        ->disabled()->dehydrated()->columnSpan(1),

                    Forms\Components\TextInput::make('impuesto')
                        ->label('IVA / Impuesto ($)')
                        ->numeric()->prefix('$')->default(0)
                        ->live(debounce: 500)
                        ->afterStateUpdated(fn(Forms\Get $get, Forms\Set $set) =>
                        self::calcularTotales($get, $set)
                        )
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('descuento')
                        ->label('Descuento Global ($)')
                        ->numeric()->prefix('$')->default(0)
                        ->live(debounce: 500)
                        ->afterStateUpdated(fn(Forms\Get $get, Forms\Set $set) =>
                        self::calcularTotales($get, $set)
                        )
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('total')
                        ->label('TOTAL')
                        ->numeric()->prefix('$')->default(0)
                        ->disabled()->dehydrated()->columnSpan(1),

                    Forms\Components\Textarea::make('condiciones_pago')
                        ->label('Condiciones de Pago')
                        ->rows(2)->columnSpan(2),

                    Forms\Components\Textarea::make('notas')
                        ->label('Notas / Instrucciones')
                        ->rows(2)->columnSpan(2),

                    Forms\Components\Textarea::make('motivo_cancelacion')
                        ->label('Motivo de Cancelación')
                        ->rows(2)->columnSpanFull()
                        ->visible(fn(Forms\Get $get) => $get('estado') === 'cancelado'),
                ]),
        ];
    }

    protected static function calcularTotales(Forms\Get $get, Forms\Set $set): void
    {
        $items = $get('items') ?? [];

        $subtotal = collect($items)->sum(fn($item) => floatval($item['subtotal'] ?? 0));

        $impuesto  = floatval($get('impuesto')  ?? 0);
        $descuento = floatval($get('descuento') ?? 0);
        $total     = round($subtotal + $impuesto - $descuento, 2);

        $set('subtotal', round($subtotal, 2));
        $set('total', $total);
    }

    public static function table(Table $table): Table
    {
        return $table
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
                    ->color(fn($record) =>
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

                Tables\Columns\TextColumn::make('recepcion_progreso')
                    ->label('Recibido')
                    ->state(fn($record) =>
                        $record->items->sum('cantidad') > 0
                            ? number_format($record->items->sum('cantidad_recibida'), 0)
                              . ' / '
                              . number_format($record->items->sum('cantidad'), 0) . ' u.'
                            : '—'
                    )
                    ->badge()
                    ->color(fn($state, $record) => match($record->estado) {
                        'recibido' => 'success',
                        'parcial'  => 'warning',
                        default    => 'gray',
                    })
                    ->toggleable(),

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
                    ->query(fn($query, array $data) =>
                    $query
                        ->when($data['desde'], fn($q, $d) => $q->whereDate('fecha_pedido', '>=', $d))
                        ->when($data['hasta'], fn($q, $d) => $q->whereDate('fecha_pedido', '<=', $d))
                    ),

                Tables\Filters\Filter::make('vencidas')
                    ->label('Vencidas (> 7 días)')
                    ->query(fn($query) =>
                    $query->whereIn('estado', ['enviado', 'confirmado'])
                        ->where('fecha_pedido', '<', now()->subDays(7))
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('enviar_proveedor')
                    ->label('Enviar')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->visible(fn($record) => $record->estado === 'borrador')
                    ->requiresConfirmation()
                    ->modalHeading('¿Enviar Orden de Compra al Proveedor?')
                    ->modalDescription('Se marcará como enviada.')
                    ->action(function ($record) {
                        if ($record->proveedor && $record->proveedor->estado !== 'activo') {
                            Notification::make()->danger()
                                ->title('No se puede enviar')
                                ->body("El proveedor \"{$record->proveedor->nombre}\" no está activo.")
                                ->send();
                            return;
                        }
                        $record->update(['estado' => 'enviado']);
                        Notification::make()->title('OC enviada al proveedor')->success()->send();
                    }),

                Tables\Actions\Action::make('confirmar_oc')
                    ->label('Confirmar')
                    ->icon('heroicon-o-check-badge')
                    ->color('primary')
                    ->visible(fn($record) => in_array($record->estado, ['borrador', 'enviado']))
                    ->requiresConfirmation()
                    ->modalHeading('Confirmar Orden de Compra')
                    ->modalDescription('¿El proveedor confirmó la orden? Se marcará como Confirmada y quedará lista para recepción.')
                    ->action(function ($record) {
                        $record->update(['estado' => 'confirmado']);
                        Notification::make()->success()->title('OC confirmada por el proveedor')->send();
                    }),

                Tables\Actions\Action::make('confirmar_recepcion')
                    ->label('Recibir')
                    ->icon('heroicon-o-inbox-arrow-down')
                    ->color('success')
                    ->visible(fn($record) => in_array($record->estado, ['enviado', 'confirmado', 'parcial']))
                    ->url(fn($record) => PedidoCompraResource::getUrl('recibir', ['record' => $record])),

                Tables\Actions\Action::make('cancelar')
                    ->label('Cancelar OC')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn($record) => !in_array($record->estado, ['recibido', 'cancelado']))
                    ->modalHeading('Cancelar Orden de Compra')
                    ->form([
                        Forms\Components\Textarea::make('motivo_cancelacion')
                            ->label('Motivo de Cancelación')
                            ->required()
                            ->minLength(10)
                            ->rows(3)
                            ->placeholder('Indique el motivo de cancelación (mínimo 10 caracteres)'),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'estado'             => 'cancelado',
                            'motivo_cancelacion' => $data['motivo_cancelacion'],
                        ]);
                        Notification::make()->success()->title('Orden de compra cancelada')->send();
                    }),
            ])
            ->modifyQueryUsing(fn($query) => $query->with('items'))
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Orden de Compra')
                ->columns(4)
                ->schema([
                    Infolists\Components\TextEntry::make('numero')->badge()->color('primary'),
                    Infolists\Components\TextEntry::make('estado')
                        ->badge()->color(fn($record) => $record->estado_color),
                    Infolists\Components\TextEntry::make('proveedor.nombre')->label('Proveedor'),
                    Infolists\Components\TextEntry::make('total')->money('USD')->weight('bold'),
                    Infolists\Components\TextEntry::make('fecha_pedido')->date('d/m/Y'),
                    Infolists\Components\TextEntry::make('fecha_requerida')->label('Requerida')->date('d/m/Y'),
                    Infolists\Components\TextEntry::make('fecha_recepcion')->label('Recibida')->date('d/m/Y'),
                    Infolists\Components\TextEntry::make('user.name')->label('Creado por'),
                ]),

            Infolists\Components\Section::make('Condiciones y Notas')
                ->columns(2)
                ->collapsed()
                ->schema([
                    Infolists\Components\TextEntry::make('condiciones_pago')->label('Condiciones de Pago'),
                    Infolists\Components\TextEntry::make('notas')->label('Notas'),
                    Infolists\Components\TextEntry::make('motivo_cancelacion')
                        ->label('Motivo de Cancelación')
                        ->columnSpanFull()
                        ->visible(fn($record) => $record->estado === 'cancelado'),
                ]),

            Infolists\Components\Section::make('Productos')
                ->schema([
                    Infolists\Components\RepeatableEntry::make('items')
                        ->label('')
                        ->schema([
                            Infolists\Components\TextEntry::make('producto.nombre')
                                ->label('Producto'),

                            Infolists\Components\TextEntry::make('cantidad')
                                ->label('Ordenado'),

                            Infolists\Components\TextEntry::make('cantidad_recibida')
                                ->label('Recibido'),

                            Infolists\Components\TextEntry::make('cantidad_pendiente')
                                ->label('Pendiente')
                                ->color(fn($state) => floatval($state) > 0 ? 'warning' : 'success'),

                            Infolists\Components\TextEntry::make('estado_item')
                                ->label('Estado')
                                ->badge()
                                ->state(fn($record) => match(true) {
                                    floatval($record->cantidad_recibida) >= floatval($record->cantidad) => 'Completo',
                                    floatval($record->cantidad_recibida) > 0                            => 'Parcial',
                                    default                                                             => 'Pendiente',
                                })
                                ->color(fn(string $state) => match($state) {
                                    'Completo'  => 'success',
                                    'Parcial'   => 'warning',
                                    'Pendiente' => 'danger',
                                    default     => 'gray',
                                }),

                            Infolists\Components\TextEntry::make('precio_unitario')
                                ->label('P. Unit.')
                                ->money('USD'),

                            Infolists\Components\TextEntry::make('subtotal')
                                ->money('USD'),
                        ])
                        ->columns(7),
                ]),

            Infolists\Components\Section::make('Historial de Cambios')
                ->collapsed()
                ->icon('heroicon-o-clock')
                ->schema([
                    Infolists\Components\RepeatableEntry::make('activities')
                        ->label('')
                        ->schema([
                            Infolists\Components\TextEntry::make('description')
                                ->label('Acción'),
                            Infolists\Components\TextEntry::make('causer.name')
                                ->label('Por')
                                ->default('Sistema'),
                            Infolists\Components\TextEntry::make('created_at')
                                ->label('Fecha')
                                ->dateTime('d/m/Y H:i'),
                        ])
                        ->columns(3),
                ]),
        ]);
    }

    public static function getNavigationBadge(): ?string
    {
        $pendientes = static::getModel()::whereIn('estado', ['enviado', 'confirmado', 'parcial'])->count();
        return $pendientes > 0 ? (string) $pendientes : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    /**
     * Genera recomendaciones de compra basadas en stock bajo mínimo
     * y promedio de ventas de los últimos 30 días.
     */

    public static function generarRecomendaciones(): array
    {
        $ocsPendientes = \App\Models\PedidoCompra::whereIn('estado', ['borrador', 'enviado', 'confirmado', 'parcial'])
            ->with('items')
            ->get();
        $productosEnOC  = [];
        $proveedoresEnOC = [];

        foreach ($ocsPendientes as $oc) {
            $proveedoresEnOC[$oc->proveedor_id][] = $oc->numero;
            foreach ($oc->items as $item) {
                $productosEnOC[$oc->proveedor_id][$item->producto_id][] = $oc->numero;
            }
        }

        $inventarios = InventarioAlmacen::with(['producto.proveedor'])
            ->where('stock_minimo', '>', 0)
            ->whereColumn('stock_actual', '<', 'stock_minimo')
            ->whereHas('producto', fn($q) => $q->activo())
            ->get()
            ->groupBy('producto_id');

        $recomendaciones = [];

        foreach ($inventarios as $productoId => $items) {
            $stockTotal = $items->sum(fn($i) => floatval($i->stock_actual));
            $stockMin   = $items->sum(fn($i) => floatval($i->stock_minimo));

            if ($stockTotal >= $stockMin) continue;

            $producto = $items->first()->producto;
            if (!$producto) continue;

            $ventasMes      = (float) MovimientoInventario::where('tipo', 'salida_venta')
                ->where('producto_id', $productoId)
                ->where('fecha_movimiento', '>=', now()->subDays(30))
                ->sum('cantidad');
            $promedioDiario = $ventasMes / 30;
            $diasProveedor  = $producto->proveedor?->tiempo_entrega_dias ?? 7;

            $cantSugerida = (int) max(
                ceil($promedioDiario * ($diasProveedor + 7)),
                ceil(($stockMin - $stockTotal) * 1.5)
            );
            if ($cantSugerida <= 0) {
                $cantSugerida = (int) ceil($stockMin - $stockTotal);
            }

            $proveedorId = $producto->proveedor_id;
            $ocProducto   = $productosEnOC[$proveedorId][$productoId] ?? [];
            $ocProveedor  = $proveedoresEnOC[$proveedorId] ?? [];

            $recomendaciones[] = [
                'producto'         => $producto->nombre,
                'proveedor'        => $producto->proveedor?->nombre ?? '— Sin proveedor —',
                'proveedor_id'     => $proveedorId,
                'producto_id'      => $producto->id,
                'stock_actual'     => round($stockTotal, 4),
                'stock_minimo'     => round($stockMin, 4),
                'prom_diario'      => round($promedioDiario, 4),
                'cant_sugerida'    => $cantSugerida,
                'precio'           => floatval($producto->precio_compra),
                'unidad_medida'    => $producto->unidad_medida ?? 'unidad',
                // ─── Nuevos campos de estado OC ───
                'en_oc'            => !empty($ocProducto),
                'oc_numeros'       => $ocProducto,
                'proveedor_en_oc'  => !empty($ocProveedor),
                'oc_proveedor_nums'=> array_unique($ocProveedor),
            ];
        }

        usort($recomendaciones, fn($a, $b) => strcmp($a['proveedor'], $b['proveedor']));

        return $recomendaciones;
    }
    public static function getPages(): array
    {
        return [
            'index'   => Pages\ListPedidoCompra::route('/'),
            'create'  => Pages\CreatePedidoCompra::route('/create'),
            'view'    => Pages\ViewPedidoCompra::route('/{record}'),
            'edit'    => Pages\EditPedidoCompra::route('/{record}/edit'),
            'recibir' => Pages\RegistrarRecepcion::route('/{record}/recibir'),
        ];
    }
}