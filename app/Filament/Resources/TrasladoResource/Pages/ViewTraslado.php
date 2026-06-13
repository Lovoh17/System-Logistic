<?php

namespace App\Filament\Resources\TrasladoResource\Pages;

use App\Filament\Resources\TrasladoResource;
use App\Models\InventarioAlmacen;
use App\Models\Transportista;
use App\Models\TrasladoItem;
use App\Models\User;
use App\Services\DistanceCalculator;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewTraslado extends ViewRecord
{
    protected static string $resource = TrasladoResource::class;

    protected static string $view = 'filament.resources.traslado-resource.pages.view-traslado';

    public function mount(int|string $record): void
    {
        parent::mount($record);
        $this->record->load([
            'almacenOrigen',
            'almacenDestino',
            'transportista.user',
            'creadoPor',
            'aprobadoPor',
            'items.producto',
        ]);
    }

    /** Distancia en km entre origen y destino (desde tabla o Haversine). */
    public function getDistanciaKm(): ?float
    {
        $a = $this->record->almacenOrigen;
        $b = $this->record->almacenDestino;
        if (! $a || ! $b) {
            return null;
        }

        return app(DistanceCalculator::class)->betweenAlmacenes($a, $b);
    }

    /** Costo estimado usando tarifa fija $0.50/km. */
    public function getCostoEstimado(): ?float
    {
        $km = $this->getDistanciaKm();

        return $km !== null ? round($km * 0.50, 2) : null;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),

            Actions\Action::make('asignar_transportista')
                ->label('Asignar Conductor')
                ->icon('heroicon-m-truck')
                ->color('info')
                ->visible(fn () => in_array($this->record->estado, ['sugerido', 'aprobado']) && ! $this->record->transportista_id)
                ->form([
                    Forms\Components\Select::make('transportista_id')
                        ->label('Conductor')
                        ->options(
                            Transportista::where('estado', 'disponible')
                                ->with('user')
                                ->get()
                                ->mapWithKeys(fn ($t) => [
                                    $t->id => ($t->user?->name ?? '—').' — '.($t->vehiculo_placa ?? 'sin placa'),
                                ])
                        )
                        ->searchable()
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->record->update(['transportista_id' => $data['transportista_id']]);
                    Notification::make()->success()->title('Conductor asignado')->send();
                    $this->record->refresh();
                }),

            Actions\Action::make('aprobar')
                ->label('Aprobar')
                ->icon('heroicon-m-check')
                ->color('info')
                ->visible(fn () => $this->record->estado === 'sugerido')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update([
                        'estado' => 'aprobado',
                        'aprobado_por' => auth()->id(),
                        'fecha_aprobacion' => now(),
                    ]);
                    Notification::make()->success()->title('Traslado aprobado')->send();
                    $this->record->refresh();
                }),

            Actions\Action::make('completar')
                ->label('Completar')
                ->icon('heroicon-m-check-circle')
                ->color('success')
                ->visible(fn () => $this->record->estado === 'aprobado')
                ->modalHeading('Completar Traslado')
                ->modalWidth('lg')
                ->form(fn () => $this->record->items->flatMap(fn ($item, $i) => [
                    Forms\Components\Hidden::make("items.{$i}.item_id")
                        ->default($item->id),

                    Forms\Components\Placeholder::make("items.{$i}.producto_nombre")
                        ->label($item->producto->nombre ?? '—')
                        ->content('Sugerido: '.number_format($item->cantidad_sugerida, 3)),

                    Forms\Components\TextInput::make("items.{$i}.cantidad_real")
                        ->label('Cantidad real recibida')
                        ->numeric()->required()->minValue(0)
                        ->default($item->cantidad_sugerida)
                        ->step(0.001),
                ])->values()->toArray())
                ->action(function (array $data) {
                    foreach ($data['items'] ?? [] as $itemData) {
                        $item = TrasladoItem::find($itemData['item_id']);
                        if (! $item) {
                            continue;
                        }

                        $cantReal = floatval($itemData['cantidad_real']);
                        $item->update(['cantidad_real' => $cantReal]);

                        $inventario = InventarioAlmacen::where('producto_id', $item->producto_id)
                            ->where('almacen_id', $this->record->almacen_destino_id)
                            ->first();

                        if ($inventario) {
                            $inventario->increment('stock_actual', $cantReal);
                        } else {
                            InventarioAlmacen::create([
                                'producto_id' => $item->producto_id,
                                'almacen_id' => $this->record->almacen_destino_id,
                                'stock_actual' => $cantReal,
                                'stock_minimo' => 0,
                                'stock_maximo' => 999999,
                                'punto_reorden' => 0,
                            ]);
                        }
                    }

                    $this->record->update([
                        'estado' => 'completado',
                        'fecha_completado' => now(),
                    ]);

                    $destAdmins = User::role('admin_sucursal')
                        ->where('almacen_id', $this->record->almacen_destino_id)
                        ->get();
                    if ($destAdmins->isNotEmpty()) {
                        Notification::make()
                            ->title('Traslado completado: '.$this->record->numero)
                            ->body('El pedido llegó a tu sucursal.')
                            ->success()
                            ->sendToDatabase($destAdmins);
                    }

                    Notification::make()->success()->title('Traslado completado. Inventario actualizado.')->send();
                    $this->record->refresh();
                }),

            Actions\Action::make('cancelar')
                ->label('Cancelar')
                ->icon('heroicon-m-x-circle')
                ->color('danger')
                ->visible(fn () => ! in_array($this->record->estado, ['completado', 'cancelado']))
                ->requiresConfirmation()
                ->modalHeading('¿Cancelar este traslado?')
                ->action(function () {
                    $this->record->update(['estado' => 'cancelado']);
                    Notification::make()->warning()->title('Traslado cancelado')->send();
                    $this->record->refresh();
                }),
        ];
    }
}
