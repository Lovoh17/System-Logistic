<?php

namespace App\Filament\Resources\PedidoCompraResource\Pages;

use App\Filament\Resources\PedidoCompraResource;
use App\Models\Producto;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;

class EditPedidoCompra extends EditRecord
{
    protected static string $resource = PedidoCompraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Encabezado de la Orden de Compra')
                ->icon('heroicon-o-document-text')
                ->columns(4)
                ->schema([
                    Forms\Components\TextInput::make('numero')
                        ->label('N° OC')
                        ->disabled()->dehydrated()->required()->columnSpan(1),

                    Forms\Components\Select::make('proveedor_id')
                        ->label('Proveedor')
                        ->relationship('proveedor', 'nombre')
                        ->searchable()->preload()->required()->columnSpan(2),

                    Forms\Components\Select::make('estado')
                        ->options([
                            'borrador'   => 'Borrador',
                            'enviado'    => 'Enviado al Proveedor',
                            'confirmado' => 'Confirmado',
                            'parcial'    => 'Parcialmente Recibido',
                            'recibido'   => 'Completamente Recibido',
                            'cancelado'  => 'Cancelado',
                        ])
                        ->required()->columnSpan(1),

                    Forms\Components\DatePicker::make('fecha_pedido')
                        ->label('Fecha del Pedido')->required()->columnSpan(1),

                    Forms\Components\DatePicker::make('fecha_requerida')
                        ->label('Fecha de Entrega Requerida')->columnSpan(1),

                    Forms\Components\DatePicker::make('fecha_recepcion')
                        ->label('Fecha Real de Recepción')->columnSpan(1),

                    Forms\Components\Select::make('moneda')
                        ->options(['USD' => '$ USD', 'EUR' => '€ EUR'])
                        ->default('USD')->columnSpan(1),
                ]),

            Forms\Components\Section::make('Productos a Ordenar')
                ->icon('heroicon-o-list-bullet')
                ->schema([
                    Forms\Components\Repeater::make('items')
                        ->relationship()->label('')->columns(6)->live()
                        ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                            self::calcularTotales($get, $set);
                        })
                        ->schema([
                            Forms\Components\Select::make('producto_id')
                                ->label('Producto')
                                ->options(Producto::activo()->pluck('nombre', 'id'))
                                ->searchable()->required()->live()
                                ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                    $p = Producto::find($state);
                                    if ($p) {
                                        $set('precio_unitario', $p->precio_compra);
                                        $set('unidad_medida', $p->unidad_medida);
                                        $set('subtotal', round(($get('cantidad') ?? 1) * $p->precio_compra, 2));
                                    }
                                    self::calcularTotales($get, $set);
                                })
                                ->columnSpan(2),

                            Forms\Components\TextInput::make('cantidad')
                                ->label('Cantidad')->numeric()->minValue(0.001)->step(0.001)
                                ->required()->live(debounce: 500)
                                ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                    $set('subtotal', round(
                                        floatval($state) * floatval($get('precio_unitario') ?? 0) * (1 - floatval($get('descuento') ?? 0) / 100), 2
                                    ));
                                    self::calcularTotales($get, $set);
                                })
                                ->columnSpan(1),

                            Forms\Components\TextInput::make('precio_unitario')
                                ->label('Precio Unit.')->numeric()->prefix('$')->required()->live(debounce: 500)
                                ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                    $set('subtotal', round(
                                        floatval($get('cantidad') ?? 0) * floatval($state) * (1 - floatval($get('descuento') ?? 0) / 100), 2
                                    ));
                                    self::calcularTotales($get, $set);
                                })
                                ->columnSpan(1),

                            Forms\Components\TextInput::make('descuento')
                                ->label('Desc. %')->numeric()->default(0)->minValue(0)->maxValue(100)->suffix('%')
                                ->live(debounce: 500)
                                ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                    $set('subtotal', round(
                                        floatval($get('cantidad') ?? 0) * floatval($get('precio_unitario') ?? 0) * (1 - floatval($state) / 100), 2
                                    ));
                                    self::calcularTotales($get, $set);
                                })
                                ->columnSpan(1),

                            Forms\Components\TextInput::make('subtotal')
                                ->label('Subtotal')->numeric()->prefix('$')->disabled()->dehydrated()->columnSpan(1),
                        ])
                        ->addActionLabel('+ Agregar Producto')
                        ->reorderable()->collapsible()
                        ->itemLabel(fn (array $state): ?string =>
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
                        ->label('Subtotal')->numeric()->prefix('$')->default(0)
                        ->disabled()->dehydrated()->columnSpan(1),

                    Forms\Components\TextInput::make('impuesto')
                        ->label('IVA / Impuesto ($)')->numeric()->prefix('$')->default(0)
                        ->live(debounce: 500)
                        ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) =>
                            self::calcularTotales($get, $set)
                        )
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('descuento')
                        ->label('Descuento Global ($)')->numeric()->prefix('$')->default(0)
                        ->live(debounce: 500)
                        ->afterStateUpdated(fn (Forms\Get $get, Forms\Set $set) =>
                            self::calcularTotales($get, $set)
                        )
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('total')
                        ->label('TOTAL')->numeric()->prefix('$')->default(0)
                        ->disabled()->dehydrated()->columnSpan(1),

                    Forms\Components\Textarea::make('condiciones_pago')
                        ->label('Condiciones de Pago')->rows(2)->columnSpan(2),

                    Forms\Components\Textarea::make('notas')
                        ->label('Notas / Instrucciones')->rows(2)->columnSpan(2),

                    Forms\Components\Textarea::make('motivo_cancelacion')
                        ->label('Motivo de Cancelación')->rows(2)->columnSpanFull()
                        ->visible(fn (Forms\Get $get) => $get('estado') === 'cancelado'),
                ]),
        ]);
    }

    protected static function calcularTotales(Forms\Get $get, Forms\Set $set): void
    {
        $items    = $get('items') ?? [];
        $subtotal = collect($items)->sum(fn ($item) => floatval($item['subtotal'] ?? 0));
        $impuesto  = floatval($get('impuesto')  ?? 0);
        $descuento = floatval($get('descuento') ?? 0);
        $total     = round($subtotal + $impuesto - $descuento, 2);

        $set('subtotal', round($subtotal, 2));
        $set('total', $total);
    }
}
