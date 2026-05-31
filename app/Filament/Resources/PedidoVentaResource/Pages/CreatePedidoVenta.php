<?php

namespace App\Filament\Resources\PedidoVentaResource\Pages;

use App\Filament\Resources\PedidoVentaResource;
use App\Models\PedidoVenta;
use App\Models\Producto;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;

class CreatePedidoVenta extends CreateRecord
{
    protected static string $resource = PedidoVentaResource::class;

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Encabezado del Pedido')
                ->icon('heroicon-o-document-text')
                ->columns(4)
                ->schema([
                    Forms\Components\TextInput::make('numero')
                        ->label('N° Pedido')
                        ->default(fn () => PedidoVenta::generarNumero())
                        ->disabled()->dehydrated()->required()->columnSpan(1),

                    Forms\Components\Select::make('cliente_id')
                        ->label('Cliente')
                        ->relationship('cliente', 'nombre')
                        ->searchable()->preload()->required()
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
                        ->default('borrador')->required()->columnSpan(1),

                    Forms\Components\DatePicker::make('fecha_pedido')
                        ->label('Fecha del Pedido')
                        ->default(now())->required()->columnSpan(1),

                    Forms\Components\DatePicker::make('fecha_requerida')
                        ->label('Fecha Requerida de Entrega')
                        ->minDate(now())->columnSpan(1),

                    Forms\Components\Select::make('prioridad')
                        ->options([
                            'baja'    => 'Baja',
                            'normal'  => 'Normal',
                            'alta'    => 'Alta',
                            'urgente' => '🔴 Urgente',
                        ])
                        ->default('normal')->required()->columnSpan(1),

                    Forms\Components\Select::make('canal_venta')
                        ->label('Canal de Venta')
                        ->options([
                            'directo'      => 'Directo',
                            'telefono'     => 'Teléfono',
                            'web'          => 'Sitio Web',
                            'distribuidor' => 'Distribuidor',
                            'whatsapp'     => 'WhatsApp',
                        ])
                        ->default('directo')->columnSpan(1),

                    Forms\Components\Select::make('almacen_id')
                        ->label('Sucursal')
                        ->relationship('almacen', 'nombre')
                        ->searchable()->preload()
                        ->default(fn () => auth()->user()->almacen_id)
                        ->disabled(fn () => auth()->user()->rol !== 'super-admin')
                        ->columnSpan(1),
                ]),

            Forms\Components\Section::make('Dirección de Entrega')
                ->icon('heroicon-o-map-pin')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('departamento_entrega')
                        ->label('Departamento')->maxLength(80)->columnSpan(1),
                    Forms\Components\TextInput::make('municipio_entrega')
                        ->label('Municipio')->maxLength(80)->columnSpan(1),
                    Forms\Components\Textarea::make('direccion_entrega')
                        ->label('Dirección Completa')->rows(2)->columnSpan(1),
                    Forms\Components\Textarea::make('instrucciones_entrega')
                        ->label('Instrucciones de Entrega')->rows(2)->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Líneas de Pedido')
                ->icon('heroicon-o-list-bullet')
                ->schema([
                    Forms\Components\Repeater::make('items')
                        ->relationship()->label('')->live()
                        ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                            $subtotal = 0;
                            foreach ($state as $item) {
                                $subtotal += $item['subtotal'] ?? 0;
                            }
                            $set('subtotal', $subtotal);
                            $impuesto = round($subtotal * 0.13, 2);
                            $set('impuesto', $impuesto);
                            $costo_envio = (float) ($get('costo_envio') ?? 0);
                            $set('total', round($subtotal + $impuesto + $costo_envio, 2));
                        })
                        ->columns(6)
                        ->schema([
                            Forms\Components\Select::make('producto_id')
                                ->label('Producto')
                                ->options(Producto::activo()->pluck('nombre', 'id'))
                                ->searchable()->required()->live()
                                ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                    $producto = Producto::find($state);
                                    if ($producto) {
                                        $set('precio_unitario', $producto->precio_venta);
                                        $set('unidad_medida', $producto->unidad_medida);
                                        $cantidad = (float) ($get('cantidad') ?? 1);
                                        $descuento = (float) ($get('descuento') ?? 0);
                                        $set('subtotal', round($cantidad * $producto->precio_venta * (1 - $descuento / 100), 2));
                                    }
                                })
                                ->columnSpan(2),

                            Forms\Components\TextInput::make('cantidad')
                                ->label('Cantidad')->numeric()->default(1)->minValue(0.001)->step(0.001)
                                ->required()->live()
                                ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                    $precio = (float) ($get('precio_unitario') ?? 0);
                                    $descuento = (float) ($get('descuento') ?? 0);
                                    $set('subtotal', round((float) $state * $precio * (1 - $descuento / 100), 2));
                                })
                                ->columnSpan(1),

                            Forms\Components\TextInput::make('precio_unitario')
                                ->label('Precio Unit.')->numeric()->prefix('$')->required()->live()
                                ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                    $cantidad = (float) ($get('cantidad') ?? 1);
                                    $descuento = (float) ($get('descuento') ?? 0);
                                    $set('subtotal', round($cantidad * (float) $state * (1 - $descuento / 100), 2));
                                })
                                ->columnSpan(1),

                            Forms\Components\TextInput::make('descuento')
                                ->label('Desc. %')->numeric()->default(0)->minValue(0)->maxValue(100)->suffix('%')
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                    $cantidad = (float) ($get('cantidad') ?? 1);
                                    $precio = (float) ($get('precio_unitario') ?? 0);
                                    $set('subtotal', round($cantidad * $precio * (1 - (float) $state / 100), 2));
                                })
                                ->columnSpan(1),

                            Forms\Components\TextInput::make('subtotal')
                                ->label('Subtotal')->numeric()->prefix('$')
                                ->disabled()->dehydrated()->default(0)->columnSpan(1),
                        ]),
                ]),

            Forms\Components\Section::make('Totales')
                ->icon('heroicon-o-calculator')
                ->columns(4)
                ->schema([
                    Forms\Components\TextInput::make('subtotal')
                        ->label('Subtotal')->numeric()->prefix('$')->default(0)
                        ->disabled()->dehydrated()->columnSpan(1),

                    Forms\Components\TextInput::make('impuesto')
                        ->label('IVA (13%)')->numeric()->prefix('$')->default(0)
                        ->disabled()->dehydrated()->columnSpan(1),

                    Forms\Components\TextInput::make('costo_envio')
                        ->label('Costo de Envío')->numeric()->prefix('$')->default(0)->live()
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            $subtotal = $get('subtotal') ?? 0;
                            $impuesto = $subtotal * 0.13;
                            $set('total', $subtotal + $impuesto + ($state ?? 0));
                        })
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('total')
                        ->label('TOTAL')->numeric()->prefix('$')->default(0)
                        ->disabled()->dehydrated()->columnSpan(1),

                    Forms\Components\Textarea::make('notas')
                        ->label('Observaciones')->rows(3)->columnSpanFull(),
                ]),
        ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        return $data;
    }
}
