<?php

namespace App\Filament\Resources\TrasladoResource\Pages;

use App\Filament\Resources\TrasladoResource;
use App\Models\Traslado;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;

class CreateTraslado extends CreateRecord
{
    protected static string $resource = TrasladoResource::class;

    public function form(Form $form): Form
    {
        $productoId     = request()->query('producto_id');
        $origenId       = request()->query('origen_id');
        $excedente      = request()->query('excedente');
        $nombreProducto = request()->query('nombre_producto');
        $origenNombre   = request()->query('origen_nombre');
        $stockActual    = request()->query('stock_actual');
        $stockMaximo    = request()->query('stock_maximo');

        return $form->schema([
            Forms\Components\Section::make('Información del Traslado')
                ->columns(4)
                ->schema([
                    Forms\Components\TextInput::make('numero')
                        ->label('N° Traslado')
                        ->default(fn () => Traslado::generarNumero())
                        ->disabled()->dehydrated()->required()->columnSpan(1),

                    Forms\Components\Select::make('producto_id')
                        ->label('Producto')
                        ->options(\App\Models\Producto::activo()->pluck('nombre', 'id'))
                        ->searchable()->preload()->required()
                        ->default($productoId)
                        ->disabled(fn () => !is_null($productoId))
                        ->helperText(fn () => $nombreProducto ? "Producto: {$nombreProducto}" : null)
                        ->columnSpan(2),

                    Forms\Components\Select::make('almacen_origen_id')
                        ->label('Sucursal Origen')
                        ->options(\App\Models\Almacen::where('activo', true)->pluck('nombre', 'id'))
                        ->required()->default($origenId)
                        ->disabled(fn () => !is_null($origenId))
                        ->helperText(fn () => $origenNombre ? "Origen: {$origenNombre}" : null)
                        ->columnSpan(1),

                    Forms\Components\Select::make('almacen_destino_id')
                        ->label('Sucursal Destino')
                        ->options(\App\Models\Almacen::where('activo', true)
                            ->where('id', '!=', $origenId)->pluck('nombre', 'id'))
                        ->required()->searchable()->columnSpan(1),
                ]),

            Forms\Components\Section::make('Información de Stock y Capacidad')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('stock_actual_info')
                        ->label('Stock Actual en Origen')
                        ->default(number_format($stockActual ?? 0, 2))
                        ->disabled()->dehydrated(false)->columnSpan(1),
                    Forms\Components\TextInput::make('stock_maximo_info')
                        ->label('Stock Máximo Permitido')
                        ->default(number_format($stockMaximo ?? 0, 2))
                        ->disabled()->dehydrated(false)->columnSpan(1),
                    Forms\Components\TextInput::make('excedente_info')
                        ->label('Excedente (Sobra)')
                        ->default(number_format($excedente ?? 0, 2))
                        ->disabled()->dehydrated(false)->columnSpan(1),
                ]),

            Forms\Components\Section::make('Cantidades')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('cantidad')
                        ->label('Cantidad a Trasladar')->numeric()->required()
                        ->minValue(1)->maxValue($excedente ?? 999999)
                        ->default($excedente ? min($excedente, 10) : 1)
                        ->helperText(fn () => $excedente ? "Máximo disponible: " . number_format($excedente, 2) : null)
                        ->columnSpan(1),

                    Forms\Components\Select::make('transportista_id')
                        ->label('Transportista (Opcional)')
                        ->options(\App\Models\Transportista::where('estado', 'disponible')->pluck('nombre', 'id'))
                        ->searchable()->preload()->columnSpan(2),

                    Forms\Components\DatePicker::make('fecha_programada')
                        ->label('Fecha Programada')
                        ->default(now()->addDays(1))->required()->columnSpan(1),

                    Forms\Components\DatePicker::make('fecha_entrega_estimada')
                        ->label('Fecha Estimada de Entrega')
                        ->default(now()->addDays(2))->columnSpan(1),
                ]),

            Forms\Components\Section::make('Información Adicional')
                ->schema([
                    Forms\Components\Textarea::make('motivo')
                        ->label('Motivo del Traslado')
                        ->default('Reubicación por excedente de inventario')->rows(2)->required(),
                    Forms\Components\Textarea::make('observaciones')
                        ->label('Observaciones')->rows(2),
                ]),
        ]);
    }
}
