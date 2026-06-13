<?php

namespace App\Filament\Sucursal\Resources;

use App\Filament\Sucursal\Resources\PedidoVentaSucursalResource\Pages;
use App\Models\PedidoVenta;
use App\Models\Producto;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PedidoVentaSucursalResource extends Resource
{
    protected static ?string $model = PedidoVenta::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationLabel = 'Ventas';

    protected static ?string $navigationGroup = 'Ventas';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Pedido de Venta';

    protected static ?string $pluralModelLabel = 'Ventas de Mi Sucursal';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('almacen_id', auth()->user()?->almacen_id);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Encabezado')->columns(4)->schema([
                Forms\Components\TextInput::make('numero')
                    ->label('N° Pedido')
                    ->default(fn () => PedidoVenta::generarNumero())
                    ->disabled()->dehydrated()->required()->columnSpan(1),

                Forms\Components\Select::make('cliente_id')
                    ->label('Cliente')->relationship('cliente', 'nombre')
                    ->searchable()->preload()->required()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('nombre')->required(),
                        Forms\Components\TextInput::make('telefono'),
                        Forms\Components\TextInput::make('email')->email(),
                    ])->columnSpan(2),

                Forms\Components\Select::make('estado')
                    ->options([
                        'borrador' => 'Borrador',
                        'confirmado' => 'Confirmado',
                        'en_preparacion' => 'En Preparación',
                        'listo' => 'Listo',
                        'entregado' => 'Entregado',
                        'cancelado' => 'Cancelado',
                    ])
                    ->default('borrador')->required()->columnSpan(1),

                Forms\Components\DatePicker::make('fecha_pedido')
                    ->label('Fecha')->default(now())->required()->columnSpan(1),

                Forms\Components\Select::make('prioridad')
                    ->options(['baja' => 'Baja', 'normal' => 'Normal', 'alta' => 'Alta', 'urgente' => '🔴 Urgente'])
                    ->default('normal')->required()->columnSpan(1),

                Forms\Components\Select::make('canal_venta')
                    ->label('Canal')
                    ->options(['directo' => 'Directo', 'telefono' => 'Teléfono', 'web' => 'Web', 'whatsapp' => 'WhatsApp'])
                    ->default('directo')->columnSpan(1),

                Forms\Components\Hidden::make('almacen_id')
                    ->default(fn () => auth()->user()?->almacen_id),
            ]),

            Forms\Components\Section::make('Líneas de Pedido')->schema([
                Forms\Components\Repeater::make('items')
                    ->relationship()->label('')->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                        $subtotal = collect($state)->sum('subtotal');
                        $set('subtotal', round($subtotal, 2));
                        $set('impuesto', round($subtotal * 0.13, 2));
                        $costo = (float) ($get('costo_envio') ?? 0);
                        $set('total', round($subtotal * 1.13 + $costo, 2));
                    })
                    ->columns(5)
                    ->schema([
                        Forms\Components\Select::make('producto_id')
                            ->label('Producto')
                            ->options(Producto::activo()->pluck('nombre', 'id'))
                            ->searchable()->required()->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                $p = Producto::find($state);
                                if ($p) {
                                    $set('precio_unitario', $p->precio_venta);
                                    $set('subtotal', round((float) ($get('cantidad') ?? 1) * $p->precio_venta, 2));
                                }
                            })->columnSpan(2),

                        Forms\Components\TextInput::make('cantidad')
                            ->label('Cantidad')->numeric()->default(1)->minValue(0.001)->required()->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                $precio = (float) ($get('precio_unitario') ?? 0);
                                $set('subtotal', round((float) $state * $precio, 2));
                            }),

                        Forms\Components\TextInput::make('precio_unitario')
                            ->label('Precio')->numeric()->prefix('$')->required()->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                $set('subtotal', round((float) ($get('cantidad') ?? 1) * (float) $state, 2));
                            }),

                        Forms\Components\TextInput::make('subtotal')
                            ->label('Subtotal')->numeric()->prefix('$')->disabled()->dehydrated()->default(0),

                        Forms\Components\TextInput::make('descuento')
                            ->label('Desc.%')->numeric()->default(0)->minValue(0)->maxValue(100)->suffix('%'),
                    ])->addActionLabel('+ Agregar Producto'),
            ]),

            Forms\Components\Section::make('Totales')->columns(4)->schema([
                Forms\Components\TextInput::make('subtotal')->label('Subtotal')->numeric()->prefix('$')
                    ->disabled()->dehydrated()->default(0),
                Forms\Components\TextInput::make('impuesto')->label('IVA 13%')->numeric()->prefix('$')
                    ->disabled()->dehydrated()->default(0),
                Forms\Components\TextInput::make('costo_envio')->label('Envío')->numeric()->prefix('$')->default(0)
                    ->live()->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                        $sub = (float) ($get('subtotal') ?? 0);
                        $set('total', round($sub * 1.13 + (float) $state, 2));
                    }),
                Forms\Components\TextInput::make('total')->label('TOTAL')->numeric()->prefix('$')
                    ->disabled()->dehydrated()->default(0),
                Forms\Components\Textarea::make('notas')->label('Observaciones')->rows(2)->columnSpanFull(),
            ]),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Pedido')->columns(4)->schema([
                Infolists\Components\TextEntry::make('numero')->label('N° Pedido')->badge()->color('primary'),
                Infolists\Components\TextEntry::make('estado')->badge()
                    ->color(fn ($state) => match ($state) {
                        'borrador' => 'gray',
                        'confirmado' => 'info',
                        'en_preparacion' => 'warning',
                        'listo' => 'primary',
                        'entregado' => 'success',
                        'cancelado' => 'danger',
                        default => 'gray',
                    }),
                Infolists\Components\TextEntry::make('cliente.nombre')->label('Cliente'),
                Infolists\Components\TextEntry::make('fecha_pedido')->label('Fecha')->date('d/m/Y'),
                Infolists\Components\TextEntry::make('prioridad')->badge()
                    ->color(fn ($state) => match ($state) {
                        'baja' => 'gray', 'normal' => 'info', 'alta' => 'warning', 'urgente' => 'danger', default => 'gray',
                    }),
                Infolists\Components\TextEntry::make('canal_venta')->label('Canal')->badge()->color('gray'),
                Infolists\Components\TextEntry::make('total')->label('Total')->money('USD'),
            ]),

            Infolists\Components\Section::make('Productos')->schema([
                Infolists\Components\RepeatableEntry::make('items')->label('')->columns(4)->schema([
                    Infolists\Components\TextEntry::make('producto.nombre')->label('Producto'),
                    Infolists\Components\TextEntry::make('cantidad')->label('Cantidad')->numeric(2),
                    Infolists\Components\TextEntry::make('precio_unitario')->label('Precio Unit.')->money('USD'),
                    Infolists\Components\TextEntry::make('subtotal')->label('Subtotal')->money('USD'),
                ]),
            ]),

            Infolists\Components\Section::make('Totales')->columns(3)->schema([
                Infolists\Components\TextEntry::make('subtotal')->label('Subtotal')->money('USD'),
                Infolists\Components\TextEntry::make('impuesto')->label('IVA 13%')->money('USD'),
                Infolists\Components\TextEntry::make('total')->label('TOTAL')->money('USD')->weight('bold'),
                Infolists\Components\TextEntry::make('notas')->label('Observaciones')->columnSpanFull()->placeholder('—'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero')
                    ->label('N° Pedido')->searchable()->sortable()->badge()->color('primary'),
                Tables\Columns\TextColumn::make('cliente.nombre')
                    ->label('Cliente')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('fecha_pedido')
                    ->label('Fecha')->date('d/m/Y')->sortable(),
                Tables\Columns\BadgeColumn::make('prioridad')
                    ->colors(['gray' => 'baja', 'info' => 'normal', 'warning' => 'alta', 'danger' => 'urgente']),
                Tables\Columns\BadgeColumn::make('estado')
                    ->colors([
                        'gray' => 'borrador',
                        'info' => 'confirmado',
                        'warning' => 'en_preparacion',
                        'primary' => 'listo',
                        'success' => 'entregado',
                        'danger' => 'cancelado',
                    ]),
                Tables\Columns\TextColumn::make('total')->label('Total')->money('USD')->sortable()->alignRight(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->options([
                        'borrador' => 'Borrador',
                        'confirmado' => 'Confirmado',
                        'en_preparacion' => 'En Preparación',
                        'listo' => 'Listo',
                        'entregado' => 'Entregado',
                        'cancelado' => 'Cancelado',
                    ])->multiple(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => in_array($record->estado, ['borrador', 'confirmado'])),
            ])
            ->headerActions([Tables\Actions\CreateAction::make()])
            ->defaultSort('created_at', 'desc');
    }

    public static function getNavigationBadge(): ?string
    {
        $id = auth()->user()?->almacen_id;
        if (! $id) {
            return null;
        }
        $count = PedidoVenta::where('almacen_id', $id)
            ->whereIn('estado', ['confirmado', 'en_preparacion'])->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPedidosVentaSucursal::route('/'),
            'create' => Pages\CreatePedidoVentaSucursal::route('/create'),
            'view' => Pages\ViewPedidoVentaSucursal::route('/{record}'),
            'edit' => Pages\EditPedidoVentaSucursal::route('/{record}/edit'),
        ];
    }
}
