<?php

namespace App\Filament\Contador\Resources\ContabilidadResource\Pages;

use App\Filament\Contador\Resources\ContabilidadResource;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Tables\Filters;
use Filament\Tables\Table;

class ListContabilidad extends ListRecords
{
    protected static string $resource = ContabilidadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Nuevo Asiento'),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero')
                    ->label('N° Asiento')
                    ->searchable()->sortable()
                    ->badge()->color('primary')->copyable(),

                Tables\Columns\TextColumn::make('fecha')
                    ->label('Fecha')
                    ->date('d/m/Y')->sortable(),

                Tables\Columns\TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->limit(45)->searchable()
                    ->tooltip(fn ($record) => $record->descripcion),

                Tables\Columns\TextColumn::make('tipo_documento_label')
                    ->label('Documento')
                    ->badge()->color('gray'),

                Tables\Columns\TextColumn::make('numero_documento')
                    ->label('N° Doc.')->searchable()->toggleable(),

                Tables\Columns\TextColumn::make('total_debe')
                    ->label('Débito')->money('USD')->sortable()->alignRight(),

                Tables\Columns\TextColumn::make('total_haber')
                    ->label('Crédito')->money('USD')->sortable()->alignRight(),

                Tables\Columns\IconColumn::make('esta_balanceado')
                    ->label('OK')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')->falseColor('danger'),

                Tables\Columns\TextColumn::make('estado')
                    ->badge()
                    ->color(fn ($record) => $record->estado_color),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filters\SelectFilter::make('estado')
                    ->options([
                        'borrador'   => 'Borrador',
                        'registrado' => 'Registrado',
                        'anulado'    => 'Anulado',
                    ])->multiple(),

                Filters\SelectFilter::make('tipo_documento')
                    ->label('Tipo Documento')
                    ->options([
                        'factura_cf'          => 'Factura Consumidor Final',
                        'ccf'                 => 'Comprobante de Crédito Fiscal',
                        'nota_debito'         => 'Nota de Débito',
                        'nota_credito'        => 'Nota de Crédito',
                        'comprobante_interno' => 'Comprobante Interno',
                        'transferencia'       => 'Transferencia Bancaria',
                    ])->multiple(),

                Filters\Filter::make('periodo')
                    ->label('Período')
                    ->form([
                        DatePicker::make('desde')->label('Desde')->native(false)->displayFormat('d/m/Y'),
                        DatePicker::make('hasta')->label('Hasta')->native(false)->displayFormat('d/m/Y'),
                    ])
                    ->query(fn ($query, $data) => $query
                        ->when($data['desde'], fn ($q, $d) => $q->whereDate('fecha', '>=', $d))
                        ->when($data['hasta'], fn ($q, $d) => $q->whereDate('fecha', '<=', $d))
                    )
                    ->columns(2),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => $record->estado !== 'anulado'),
                Tables\Actions\Action::make('registrar')
                    ->label('Registrar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->estado === 'borrador' && $record->esta_balanceado)
                    ->requiresConfirmation()
                    ->modalHeading('¿Registrar asiento contable?')
                    ->modalDescription('El asiento se marcará como registrado y afectará los saldos contables.')
                    ->action(fn ($record) => $record->update(['estado' => 'registrado'])),
                Tables\Actions\Action::make('anular')
                    ->label('Anular')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record->estado === 'registrado')
                    ->requiresConfirmation()
                    ->modalHeading('¿Anular asiento contable?')
                    ->modalDescription('Esta acción es irreversible. El asiento quedará anulado y no afectará los saldos.')
                    ->action(fn ($record) => $record->update(['estado' => 'anulado'])),
            ])
            ->bulkActions([])
            ->defaultSort('fecha', 'desc')
            ->striped()
            ->paginated([15, 25, 50]);
    }
}
