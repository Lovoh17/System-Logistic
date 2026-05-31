<?php

namespace App\Filament\Contador\Resources\ContabilidadResource\Pages;

use App\Filament\Contador\Resources\ContabilidadResource;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;

class EditContabilidad extends EditRecord
{
    protected static string $resource = ContabilidadResource::class;

    public function mount(int | string $record): void
    {
        parent::mount($record);

        if ($this->record->estado === 'anulado') {
            $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\Action::make('registrar')
                ->label('Registrar')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => $this->record->estado === 'borrador' && $this->record->esta_balanceado)
                ->requiresConfirmation()
                ->modalHeading('¿Registrar asiento?')
                ->action(fn () => $this->record->update(['estado' => 'registrado'])),
            Actions\Action::make('anular')
                ->label('Anular')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => $this->record->estado === 'registrado')
                ->requiresConfirmation()
                ->modalHeading('¿Anular asiento contable?')
                ->modalDescription('Esta acción es irreversible.')
                ->action(fn () => $this->record->update(['estado' => 'anulado'])),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }

    protected function afterSave(): void
    {
        $this->record->refresh();
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
                        ->disabled()->dehydrated()
                        ->columnSpan(1),

                    DatePicker::make('fecha')
                        ->label('Fecha')
                        ->required()
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->columnSpan(1),

                    Select::make('estado')
                        ->options(['borrador' => 'Borrador', 'registrado' => 'Registrado'])
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
                        ->columnSpan(1),

                    TextInput::make('numero_documento')
                        ->label('N° de Documento de Respaldo')
                        ->maxLength(60)
                        ->columnSpan(2),

                    Textarea::make('notas')
                        ->label('Notas adicionales')
                        ->rows(2)->columnSpanFull(),
                ]),

            Section::make('Partidas del Asiento (Cargo / Abono)')
                ->icon('heroicon-o-list-bullet')
                ->schema([
                    Repeater::make('lineas')
                        ->relationship()
                        ->label('')
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
