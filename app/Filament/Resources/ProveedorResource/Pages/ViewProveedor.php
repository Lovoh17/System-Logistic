<?php

namespace App\Filament\Resources\ProveedorResource\Pages;

use App\Filament\Resources\ProveedorResource;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewProveedor extends ViewRecord
{
    protected static string $resource = ProveedorResource::class;

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
            Infolists\Components\Section::make('Datos del Proveedor')
                ->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('codigo')->badge()->color('gray'),
                    Infolists\Components\TextEntry::make('nombre')->weight('bold'),
                    Infolists\Components\TextEntry::make('razon_social')->label('Razón Social'),
                    Infolists\Components\TextEntry::make('categoria_label')->label('Categoría'),
                    Infolists\Components\TextEntry::make('nit')->label('NIT'),
                    Infolists\Components\TextEntry::make('estado')
                        ->badge()->color(fn ($record) => $record->estado_color),
                ]),

            Infolists\Components\Section::make('Contacto')
                ->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('email')->copyable(),
                    Infolists\Components\TextEntry::make('telefono'),
                    Infolists\Components\TextEntry::make('celular'),
                    Infolists\Components\TextEntry::make('contacto_nombre')->label('Contacto Principal'),
                    Infolists\Components\TextEntry::make('contacto_email')->label('Email del Contacto'),
                    Infolists\Components\TextEntry::make('contacto_telefono')->label('Teléfono del Contacto'),
                ]),

            Infolists\Components\Section::make('Ubicación')
                ->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('pais')->label('País'),
                    Infolists\Components\TextEntry::make('departamento'),
                    Infolists\Components\TextEntry::make('municipio'),
                    Infolists\Components\TextEntry::make('direccion')->label('Dirección')->columnSpanFull(),
                ]),

            Infolists\Components\Section::make('Condiciones Comerciales')
                ->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('tiempo_entrega_dias')
                        ->label('Tiempo de Entrega')->suffix(' días'),
                    Infolists\Components\TextEntry::make('calificacion')
                        ->label('Calificación')->suffix(' / 5'),
                    Infolists\Components\TextEntry::make('notas')
                        ->label('Notas')->columnSpanFull(),
                ]),
        ]);
    }
}
