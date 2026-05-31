<?php

namespace App\Filament\Contador\Resources\ContabilidadResource\Pages;

use App\Filament\Contador\Resources\ContabilidadResource;
use Filament\Actions;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\FontWeight;

class ViewContabilidad extends ViewRecord
{
    protected static string $resource = ContabilidadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn () => $this->record->estado !== 'anulado'),
            Actions\Action::make('registrar')
                ->label('Registrar')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => $this->record->estado === 'borrador' && $this->record->esta_balanceado)
                ->requiresConfirmation()
                ->modalHeading('¿Registrar asiento contable?')
                ->modalDescription('El asiento se marcará como registrado y afectará los saldos contables.')
                ->action(fn () => $this->record->update(['estado' => 'registrado'])),
            Actions\Action::make('anular')
                ->label('Anular')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => $this->record->estado === 'registrado')
                ->requiresConfirmation()
                ->modalHeading('¿Anular asiento contable?')
                ->modalDescription('Esta acción es irreversible. El asiento quedará anulado y no afectará los saldos.')
                ->action(fn () => $this->record->update(['estado' => 'anulado'])),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Datos del Asiento')
                ->icon('heroicon-o-document-text')
                ->columns(3)
                ->schema([
                    TextEntry::make('numero')
                        ->label('N° Asiento')
                        ->badge()->color('primary'),

                    TextEntry::make('fecha')
                        ->label('Fecha')
                        ->date('d/m/Y'),

                    TextEntry::make('estado')
                        ->label('Estado')
                        ->badge()
                        ->color(fn ($record) => $record->estado_color),

                    TextEntry::make('tipo_documento_label')
                        ->label('Tipo de Documento'),

                    TextEntry::make('numero_documento')
                        ->label('N° Documento')
                        ->placeholder('—'),

                    TextEntry::make('user.name')
                        ->label('Registrado por')
                        ->placeholder('—'),

                    TextEntry::make('descripcion')
                        ->label('Descripción')
                        ->columnSpanFull(),

                    TextEntry::make('notas')
                        ->label('Notas')
                        ->columnSpanFull()
                        ->placeholder('Sin notas'),
                ]),

            Section::make('Partidas del Asiento')
                ->icon('heroicon-o-list-bullet')
                ->schema([
                    RepeatableEntry::make('lineas')
                        ->label('')
                        ->columns(5)
                        ->schema([
                            TextEntry::make('cuenta.codigo')
                                ->label('Código')
                                ->badge()->color('gray'),

                            TextEntry::make('cuenta.nombre')
                                ->label('Cuenta'),

                            TextEntry::make('descripcion')
                                ->label('Descripción')
                                ->placeholder('—'),

                            TextEntry::make('debe')
                                ->label('Débito')
                                ->money('USD')
                                ->color(fn ($state) => $state > 0 ? 'info' : 'gray'),

                            TextEntry::make('haber')
                                ->label('Crédito')
                                ->money('USD')
                                ->color(fn ($state) => $state > 0 ? 'success' : 'gray'),
                        ]),
                ]),

            Section::make('Verificación de Cuadre')
                ->icon('heroicon-o-calculator')
                ->columns(3)
                ->schema([
                    TextEntry::make('total_debe')
                        ->label('Total Débito')
                        ->money('USD')
                        ->weight(FontWeight::Bold),

                    TextEntry::make('total_haber')
                        ->label('Total Crédito')
                        ->money('USD')
                        ->weight(FontWeight::Bold),

                    TextEntry::make('esta_balanceado')
                        ->label('Cuadre')
                        ->formatStateUsing(fn ($state) => $state ? 'BALANCEADO' : 'DESBALANCEADO')
                        ->badge()
                        ->color(fn ($record) => $record->esta_balanceado ? 'success' : 'danger'),
                ]),
        ]);
    }
}
