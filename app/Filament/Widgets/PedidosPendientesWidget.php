<?php

namespace App\Filament\Widgets;

use App\Models\PedidoVenta;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PedidosPendientesWidget extends BaseWidget
{
    protected static ?string $heading = '📋 Pedidos Pendientes de Atención';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                PedidoVenta::query()
                    ->whereIn('estado', ['confirmado', 'en_preparacion'])
                    ->with(['cliente'])
                    ->orderBy('fecha_requerida')
                    ->limit(8)
            )
            ->columns([
                Tables\Columns\TextColumn::make('numero')
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('cliente.nombre')
                    ->label('Cliente')
                    ->limit(20),
                Tables\Columns\TextColumn::make('fecha_requerida')
                    ->label('Requerido')
                    ->date('d/m/Y')
                    ->color(fn ($record) => $record->fecha_requerida?->isPast() ? 'danger' : null),
                Tables\Columns\BadgeColumn::make('prioridad')
                    ->colors([
                        'gray'    => 'baja',
                        'info'    => 'normal',
                        'warning' => 'alta',
                        'danger'  => 'urgente',
                    ]),
                Tables\Columns\TextColumn::make('total')
                    ->money('USD'),
            ])
            ->actions([
                Tables\Actions\Action::make('ver')
                    ->url(fn ($record) => route('filament.admin.resources.pedidos-venta.view', $record))
                    ->icon('heroicon-m-eye'),
            ]);
    }
}

// ──────────────────────────────────────────



// ──────────────────────────────────────────


