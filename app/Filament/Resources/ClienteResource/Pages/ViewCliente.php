<?php

namespace App\Filament\Resources\ClienteResource\Pages;

use App\Filament\Resources\ClienteResource;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewCliente extends ViewRecord
{
    protected static string $resource = ClienteResource::class;


    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Datos del Cliente')
                ->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('codigo')
                        ->label('Código')
                        ->badge()
                        ->color('gray')
                        ->columnSpan(1),

                    Infolists\Components\TextEntry::make('nombre')
                        ->label('Nombre / Razón Comercial')
                        ->columnSpan(2),

                    Infolists\Components\TextEntry::make('razon_social')
                        ->label('Razón Social Legal')
                        ->columnSpan(2),

                    Infolists\Components\TextEntry::make('tipo')
                        ->label('Tipo de Cliente')
                        ->badge()
                        ->columnSpan(1),

                    Infolists\Components\TextEntry::make('nit')
                        ->label('NIT')
                        ->columnSpan(1),

                    Infolists\Components\TextEntry::make('dui')
                        ->label('DUI')
                        ->columnSpan(1),

                    Infolists\Components\TextEntry::make('estado')
                        ->label('Estado')
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'activo'    => 'success',
                            'inactivo'  => 'gray',
                            'bloqueado' => 'danger',
                            default     => 'gray',
                        })
                        ->columnSpan(1),
                ]),

            Infolists\Components\Section::make('Contacto')
                ->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('email')
                        ->label('Correo Electrónico')
                        ->copyable()
                        ->columnSpan(1),

                    Infolists\Components\TextEntry::make('telefono')
                        ->label('Teléfono')
                        ->columnSpan(1),

                    Infolists\Components\TextEntry::make('celular')
                        ->label('Celular / WhatsApp')
                        ->columnSpan(1),

                    Infolists\Components\TextEntry::make('pais')
                        ->label('País')
                        ->columnSpan(1),
                ]),

            Infolists\Components\Section::make('Condiciones Comerciales')
                ->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('limite_credito')
                        ->label('Límite de Crédito')
                        ->money('USD')
                        ->columnSpan(1),

                    Infolists\Components\TextEntry::make('dias_credito')
                        ->label('Días de Crédito')
                        ->suffix(' días')
                        ->columnSpan(1),

                    Infolists\Components\TextEntry::make('notas')
                        ->label('Observaciones')
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
