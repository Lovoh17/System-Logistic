<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PedidoCompraResource\Pages;
use App\Models\PedidoCompra;
use App\Models\Producto;
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

    protected static ?string $navigationIcon  = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel = 'Órdenes de Compra';
    protected static ?string $navigationGroup = 'Pedidos';
    protected static ?int    $navigationSort  = 1;
    protected static ?string $modelLabel      = 'Orden de Compra';
    protected static ?string $pluralModelLabel = 'Órdenes de Compra';

    public static function form(Form $form): Form
    {
        return $form->schema(self::getFormSchema());
    }

    public static function getFormSchema(): array
    {
        $productoId        = request()->query('producto_id');
        $cantidadNecesaria = request()->query('cantidad_necesaria');
        $proveedorId       = request()->query('proveedor_id');
        $producto          = $productoId ? Producto::find($productoId) : null;

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
                        ->default($proveedorId)
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
                        ->columns(6)
                        ->live()
                        ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                            self::calcularTotales($get, $set);
                        })
                        ->defaultItems($producto ? 1 : 0)
                        ->default($producto ? [[
                            'producto_id'     => $producto->id,
                            'cantidad'        => $cantidadNecesaria ?? $producto->stock_minimo,
                            'precio_unitario' => $producto->precio_compra,
                            'unidad_medida'   => $producto->unidad_medida,
                            'subtotal'        => ($cantidadNecesaria ?? $producto->stock_minimo) * $producto->precio_compra,
                        ]] : [])
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
                                        $set('unidad_medida',   $p->unidad_medida);
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
                                    $set('subtotal', round(
                                        floatval($state) * floatval($get('precio_unitario') ?? 0) * (1 - floatval($get('descuento') ?? 0) / 100), 2
                                    ));
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

    // ✅ Calcula subtotal y total general en tiempo real
    protected static function calcularTotales(Forms\Get $get, Forms\Set $set): void
    {
        $items = $get('items') ?? [];

        $subtotal = collect($items)
            ->sum(fn($item) => floatval($item['subtotal'] ?? 0));

        $impuesto  = floatval($get('impuesto')  ?? 0);
        $descuento = floatval($get('descuento') ?? 0);
        $total     = round($subtotal + $impuesto - $descuento, 2);

        \Log::info('[OC] Totales recalculados', [
            'subtotal'  => $subtotal,
            'impuesto'  => $impuesto,
            'descuento' => $descuento,
            'total'     => $total,
        ]);

        $set('subtotal', round($subtotal, 2));
        $set('total',    $total);
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
                    ->modalDescription('Se marcará como enviada y se notificará al proveedor.')
                    ->action(function ($record) {
                        $record->update(['estado' => 'enviado']);
                        Notification::make()->title('OC enviada al proveedor')->success()->send();
                    }),

                Tables\Actions\Action::make('confirmar_recepcion')
                    ->label('Recibir')
                    ->icon('heroicon-o-inbox-arrow-down')
                    ->color('success')
                    ->visible(fn($record) => in_array($record->estado, ['enviado', 'confirmado', 'parcial']))
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

            Infolists\Components\Section::make('Productos')
                ->schema([
                    Infolists\Components\RepeatableEntry::make('items')
                        ->label('')
                        ->schema([
                            Infolists\Components\TextEntry::make('producto.nombre')->label('Producto'),
                            Infolists\Components\TextEntry::make('cantidad'),
                            Infolists\Components\TextEntry::make('cantidad_recibida')->label('Recibida'),
                            Infolists\Components\TextEntry::make('precio_unitario')->label('P. Unit.')->money('USD'),
                            Infolists\Components\TextEntry::make('subtotal')->money('USD'),
                        ])
                        ->columns(5),
                ]),
        ]);
    }

    public static function getNavigationBadge(): ?string
    {
        $pendientes = static::getModel()::whereIn('estado', ['enviado', 'confirmado'])->count();
        return $pendientes > 0 ? (string) $pendientes : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPedidoCompra::route('/'),
            'create' => Pages\CreatePedidoCompra::route('/create'),
            'view'   => Pages\ViewPedidoCompra::route('/{record}'),
            'edit'   => Pages\EditPedidoCompra::route('/{record}/edit'),
        ];
    }
}