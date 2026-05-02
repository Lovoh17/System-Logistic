<?php
namespace App\Filament\Widgets;

use App\Models\Envio;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class EnviosActivosWidget extends BaseWidget
{
    protected static ?string $heading = '🚛 Envíos en Tránsito';
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Envio::query()
                    ->whereIn('estado', ['despachado', 'en_transito', 'en_destino'])
                    ->with(['pedidoVenta.cliente', 'transportista'])
                    ->orderBy('fecha_entrega_estimada')
            )
            ->columns([
                Tables\Columns\TextColumn::make('numero')
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('pedidoVenta.cliente.nombre')
                    ->label('Cliente'),
                Tables\Columns\TextColumn::make('transportista.nombre')
                    ->label('Transportista'),
                Tables\Columns\TextColumn::make('destino_municipio')
                    ->label('Destino')
                    ->description(fn($record) => $record->destino_departamento),
                Tables\Columns\TextColumn::make('fecha_entrega_estimada')
                    ->label('ETA')
                    ->dateTime('d/m/Y H:i')
                    ->color(fn ($record) => $record->fecha_entrega_estimada?->isPast() ? 'danger' : 'success'),
                Tables\Columns\BadgeColumn::make('estado')
                    ->colors([
                        'info'    => 'despachado',
                        'primary' => 'en_transito',
                        'indigo'  => 'en_destino',
                    ]),
                Tables\Columns\TextColumn::make('numero_seguimiento')
                    ->label('Tracking')
                    ->badge()
                    ->color('gray'),
            ])
            ->actions([
                Tables\Actions\Action::make('seguimiento')
                    ->label('Ver Tracking')
                    ->url(fn ($record) => route('filament.admin.resources.envios.view', $record))
                    ->icon('heroicon-m-map-pin'),
            ]);
    }
}