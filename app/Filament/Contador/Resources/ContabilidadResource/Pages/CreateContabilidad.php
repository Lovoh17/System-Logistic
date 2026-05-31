<?php

namespace App\Filament\Contador\Resources\ContabilidadResource\Pages;

use App\Filament\Contador\Resources\ContabilidadResource;
use App\Models\AsientoContable;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;

class CreateContabilidad extends CreateRecord
{
    protected static string $resource = ContabilidadResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }

    protected function afterCreate(): void
    {
        $this->record->recalcularTotales();
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Encabezado del Asiento')
                ->icon('heroicon-o-document-text')
                ->columns(3)
                ->schema([
                    TextInput::make('numero')
                        ->label('N° Asiento')
                        ->default(fn () => AsientoContable::generarNumero())
                        ->disabled()->dehydrated()->required()
                        ->columnSpan(1),

                    DatePicker::make('fecha')
                        ->label('Fecha')
                        ->required()
                        ->default(today())
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->columnSpan(1),

                    Select::make('estado')
                        ->options(['borrador' => 'Borrador', 'registrado' => 'Registrado'])
                        ->default('borrador')
                        ->required()
                        ->columnSpan(1),

                    Textarea::make('descripcion')
                        ->label('Descripción del Asiento')
                        ->required()->rows(2)->columnSpanFull(),

                    Select::make('tipo_documento')
                        ->label('Tipo de Documento de Respaldo')
                        ->options([
                            'factura_cf'          => 'Factura Consumidor Final',
                            'ccf'                 => 'Comprobante de Crédito Fiscal',
                            'nota_debito'         => 'Nota de Débito',
                            'nota_credito'        => 'Nota de Crédito',
                            'comprobante_interno' => 'Comprobante Interno',
                            'transferencia'       => 'Transferencia Bancaria',
                        ])
                        ->required()
                        ->default('comprobante_interno')
                        ->columnSpan(1),

                    TextInput::make('numero_documento')
                        ->label('N° de Documento de Respaldo')
                        ->placeholder('Ej: F-0001, CCF-1234...')
                        ->maxLength(60)
                        ->columnSpan(2),

                    Textarea::make('notas')
                        ->label('Notas adicionales')
                        ->rows(2)->columnSpanFull(),
                ]),

            Section::make('Partidas del Asiento (Cargo / Abono)')
                ->icon('heroicon-o-list-bullet')
                ->description('Ingrese al menos dos partidas. Los totales de Débito y Crédito deben ser iguales (partida doble).')
                ->schema([
                    Repeater::make('lineas')
                        ->relationship()
                        ->label('')
                        ->defaultItems(2)
                        ->addActionLabel('Agregar partida')
                        ->columns(5)
                        ->schema([
                            Select::make('cuenta_contable_id')
                                ->label('Cuenta Contable')
                                ->options(fn () => ContabilidadResource::cuentasOptions())
                                ->searchable()
                                ->required()
                                ->columnSpan(2),

                            TextInput::make('descripcion')
                                ->label('Descripción')
                                ->maxLength(255)
                                ->columnSpan(1),

                            TextInput::make('debe')
                                ->label('Débito ($)')
                                ->numeric()->prefix('$')
                                ->default(0)->minValue(0)->step('0.01')
                                ->columnSpan(1),

                            TextInput::make('haber')
                                ->label('Crédito ($)')
                                ->numeric()->prefix('$')
                                ->default(0)->minValue(0)->step('0.01')
                                ->columnSpan(1),
                        ]),
                ]),
        ]);
    }
}
