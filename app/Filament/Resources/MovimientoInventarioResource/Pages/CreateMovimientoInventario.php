<?php

namespace App\Filament\Resources\MovimientoInventarioResource\Pages;

use App\Filament\Resources\MovimientoInventarioResource;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;

class CreateMovimientoInventario extends CreateRecord
{
    protected static string $resource = MovimientoInventarioResource::class;

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Ajuste Manual de Inventario')
                ->icon('heroicon-o-adjustments-horizontal')
                ->columns(3)
                ->schema([
                    Forms\Components\Select::make('producto_id')
                        ->label('Producto')
                        ->relationship('producto', 'nombre')
                        ->searchable()->preload()->required()->live()
                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                            if (!$state) {
                                $set('costo_unitario', null);
                                return;
                            }
                            $producto = Producto::find($state);
                            if ($producto) {
                                $set('costo_unitario', $producto->precio_venta ?? $producto->precio ?? $producto->costo ?? 0);
                            }
                        })
                        ->validationMessages(['required' => 'Debe seleccionar un producto.'])
                        ->columnSpan(2),

                    Forms\Components\Select::make('tipo')
                        ->label('Tipo de Movimiento')
                        ->options([
                            'ajuste_positivo'    => 'Ajuste Positivo (Entrada)',
                            'ajuste_negativo'    => 'Ajuste Negativo (Salida)',
                            'merma'              => 'Merma / Pérdida',
                            'inventario_inicial' => 'Inventario Inicial',
                        ])
                        ->required()
                        ->validationMessages(['required' => 'Debe seleccionar un tipo de movimiento.'])
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('cantidad')
                        ->label('Cantidad (unidades enteras)')
                        ->numeric()->required()->integer()->minValue(1)->step(1)
                        ->rules(['required', 'integer', 'min:1'])
                        ->validationMessages([
                            'required' => 'La cantidad es obligatoria.',
                            'integer'  => 'La cantidad debe ser un número entero.',
                            'min'      => 'La cantidad mínima es 1.',
                        ])
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('costo_unitario')
                        ->label('Costo Unitario ($)')
                        ->numeric()->required()->prefix('$')->minValue(0)->step(0.0001)
                        ->rules(['required', 'numeric', 'min:0'])
                        ->validationMessages([
                            'required' => 'El costo unitario es obligatorio.',
                            'numeric'  => 'Ingrese un valor numérico válido.',
                            'min'      => 'El costo no puede ser negativo.',
                        ])
                        ->columnSpan(1),

                    Forms\Components\DateTimePicker::make('fecha_movimiento')
                        ->label('Fecha y Hora')
                        ->default(now())->required()->maxDate(now())
                        ->rules(['required', 'before_or_equal:now'])
                        ->validationMessages([
                            'required'        => 'La fecha es obligatoria.',
                            'before_or_equal' => 'La fecha no puede ser futura.',
                        ])
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('lote')
                        ->label('Lote')->maxLength(50)
                        ->rules(['nullable', 'string', 'max:50'])
                        ->validationMessages(['max' => 'El lote no puede superar 50 caracteres.'])
                        ->columnSpan(1),

                    Forms\Components\DatePicker::make('fecha_vencimiento')
                        ->label('Fecha de Vencimiento')->minDate(now())
                        ->rules(['nullable', 'date', 'after_or_equal:today'])
                        ->validationMessages(['after_or_equal' => 'La fecha de vencimiento no puede ser en el pasado.'])
                        ->columnSpan(1),

                    Forms\Components\Textarea::make('motivo')
                        ->label('Motivo / Justificación')
                        ->required()->minLength(10)->maxLength(500)->rows(3)
                        ->rules(['required', 'string', 'min:10', 'max:500'])
                        ->validationMessages([
                            'required' => 'El motivo es obligatorio.',
                            'min'      => 'El motivo debe tener al menos 10 caracteres.',
                            'max'      => 'El motivo no puede superar 500 caracteres.',
                        ])
                        ->columnSpanFull(),
                ]),
        ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['numero']  = MovimientoInventario::generarNumero();
        $data['user_id'] = auth()->id();

        $producto             = Producto::findOrFail($data['producto_id']);
        $data['stock_anterior'] = $producto->stock_actual;

        $esEntrada = in_array($data['tipo'], [
            'ajuste_positivo', 'inventario_inicial', 'traslado_entrada',
        ]);

        $data['stock_nuevo'] = $esEntrada
            ? $producto->stock_actual + $data['cantidad']
            : max(0, $producto->stock_actual - $data['cantidad']);

        if (isset($data['costo_unitario'])) {
            $data['costo_total'] = $data['cantidad'] * $data['costo_unitario'];
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $movimiento = $this->record;
        $movimiento->producto->update(['stock_actual' => $movimiento->stock_nuevo]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
