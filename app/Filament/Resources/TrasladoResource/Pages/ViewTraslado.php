<?php

namespace App\Filament\Resources\TrasladoResource\Pages;

use App\Filament\Resources\TrasladoResource;
use App\Models\InventarioAlmacen;
use App\Models\Transportista;
use App\Models\User;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewTraslado extends ViewRecord
{
    protected static string $resource = TrasladoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),

            Actions\Action::make('asignar_transporte')
                ->label('Asignar Transporte')
                ->icon('heroicon-m-truck')
                ->color('info')
                ->visible(fn() => $this->record->estado === 'pendiente')
                ->form([
                    Forms\Components\Select::make('transportista_id')
                        ->label('Transportista')
                        ->options(Transportista::where('estado', 'disponible')->pluck('nombre', 'id'))
                        ->required(),
                    Forms\Components\DatePicker::make('fecha_salida')
                        ->label('Fecha de Salida')
                        ->default(now())
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'transportista_id' => $data['transportista_id'],
                        'fecha_salida'     => $data['fecha_salida'],
                        'estado'           => 'asignado',
                        'asignado_por'     => auth()->id(),
                    ]);
                    Notification::make()->success()->title('Transporte asignado')->send();

                    $destinoAdmins = User::role('admin_sucursal')
                        ->where('almacen_id', $this->record->almacen_destino_id)
                        ->get();
                    if ($destinoAdmins->isNotEmpty()) {
                        Notification::make()
                            ->title('Traslado en camino: ' . $this->record->numero)
                            ->body('Se ha asignado transporte. Llegará el ' . $data['fecha_salida'] . '.')
                            ->info()
                            ->sendToDatabase($destinoAdmins);
                    }

                    $this->record->refresh();
                }),

            Actions\Action::make('iniciar_transito')
                ->label('Iniciar Tránsito')
                ->icon('heroicon-m-play')
                ->color('primary')
                ->visible(fn() => $this->record->estado === 'asignado')
                ->requiresConfirmation()
                ->modalHeading('¿Iniciar el tránsito?')
                ->modalDescription('El traslado pasará a estado "En Tránsito".')
                ->action(function () {
                    $this->record->update(['estado' => 'en_transito']);
                    Notification::make()->success()->title('Traslado en tránsito')->send();

                    $destinoAdmins = User::role('admin_sucursal')
                        ->where('almacen_id', $this->record->almacen_destino_id)
                        ->get();
                    if ($destinoAdmins->isNotEmpty()) {
                        Notification::make()
                            ->title('Traslado en tránsito: ' . $this->record->numero)
                            ->body('El pedido está en camino a tu sucursal.')
                            ->warning()
                            ->sendToDatabase($destinoAdmins);
                    }

                    $this->record->refresh();
                }),

            Actions\Action::make('completar_entrega')
                ->label('Completar Entrega')
                ->icon('heroicon-m-check-circle')
                ->color('success')
                ->visible(fn() => $this->record->estado === 'en_transito')
                ->form([
                    Forms\Components\DatePicker::make('fecha_entrega_real')
                        ->label('Fecha de Entrega')
                        ->default(now())
                        ->required(),
                    Forms\Components\TextInput::make('cantidad_recibida')
                        ->label('Cantidad Recibida')
                        ->numeric()
                        ->default(fn() => $this->record->cantidad)
                        ->required()
                        ->minValue(0)
                        ->maxValue(fn() => $this->record->cantidad),
                ])
                ->action(function (array $data) {
                    $inventario = InventarioAlmacen::where('producto_id', $this->record->producto_id)
                        ->where('almacen_id', $this->record->almacen_destino_id)
                        ->first();

                    if ($inventario) {
                        $inventario->increment('stock_actual', $data['cantidad_recibida']);
                    } else {
                        InventarioAlmacen::create([
                            'producto_id'   => $this->record->producto_id,
                            'almacen_id'    => $this->record->almacen_destino_id,
                            'stock_actual'  => $data['cantidad_recibida'],
                            'stock_minimo'  => 0,
                            'stock_maximo'  => 999999,
                            'punto_reorden' => 0,
                        ]);
                    }

                    $this->record->update([
                        'estado'             => 'entregado',
                        'fecha_entrega_real' => $data['fecha_entrega_real'],
                        'cantidad_recibida'  => $data['cantidad_recibida'],
                    ]);

                    Notification::make()
                        ->success()
                        ->title('Entrega completada')
                        ->body('El inventario ha sido actualizado.')
                        ->send();

                    $destinoAdmins = User::role('admin_sucursal')
                        ->where('almacen_id', $this->record->almacen_destino_id)
                        ->get();
                    if ($destinoAdmins->isNotEmpty()) {
                        Notification::make()
                            ->title('Traslado entregado: ' . $this->record->numero)
                            ->body('Stock actualizado. Confirma la recepción en tu panel.')
                            ->success()
                            ->sendToDatabase($destinoAdmins);
                    }

                    $this->record->refresh();
                }),

            Actions\Action::make('cancelar')
                ->label('Cancelar Traslado')
                ->icon('heroicon-m-x-circle')
                ->color('danger')
                ->visible(fn() => !in_array($this->record->estado, ['entregado', 'cancelado']))
                ->requiresConfirmation()
                ->modalHeading('¿Cancelar este traslado?')
                ->modalDescription('Esta acción no se puede deshacer.')
                ->form([
                    Forms\Components\Textarea::make('motivo_cancelacion')
                        ->label('Motivo de cancelación')
                        ->required()
                        ->minLength(10)
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'estado'        => 'cancelado',
                        'observaciones' => $data['motivo_cancelacion'],
                    ]);
                    Notification::make()->warning()->title('Traslado cancelado')->send();
                    $this->record->refresh();
                }),
        ];
    }
}