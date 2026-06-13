<?php

namespace App\Filament\Sucursal\Resources\TrasladoSucursalResource\Pages;

use App\Filament\Sucursal\Resources\TrasladoSucursalResource;
use App\Models\TrasladoItem;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\HtmlString;

class ViewTrasladoSucursal extends ViewRecord
{
    protected static string $resource = TrasladoSucursalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Despacho por la sucursal de ORIGEN: descuenta su stock (vía TrasladoObserver).
            Action::make('despachar')
                ->label('Despachar Traslado')
                ->icon('heroicon-m-truck')
                ->color('warning')
                ->visible(fn () => $this->record->estado === 'aprobado'
                    && $this->record->almacen_origen_id === auth()->user()?->almacen_id
                )
                ->requiresConfirmation()
                ->modalHeading('Despachar Traslado')
                ->modalDescription('Se descontará el stock de tu sucursal y el traslado quedará en tránsito hacia el destino.')
                ->action(function () {
                    $this->record->update(['estado' => 'en_transito']);
                    Notification::make()->success()->title('Traslado despachado')->body('El stock fue descontado de tu sucursal.')->send();
                    $this->record->refresh();
                }),

            Action::make('aceptar_traslado')
                ->label('Aceptar y Recibir Traslado')
                ->icon('heroicon-m-inbox-arrow-down')
                ->color('success')
                ->visible(fn () => $this->record->estado === 'en_transito'
                    && $this->record->almacen_destino_id === auth()->user()?->almacen_id
                )
                ->modalHeading('Confirmar Cantidades Recibidas')
                ->modalDescription('Revisa cada producto e ingresa la cantidad real recibida. El inventario de tu sucursal se actualizara al confirmar.')
                ->modalWidth('2xl')
                ->form(function () {
                    $fields = [];

                    // Cabecera — una sola fila HTML completa
                    $fields[] = Forms\Components\Placeholder::make('_header')
                        ->label('')
                        ->content(new HtmlString('
                            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;padding-bottom:10px;border-bottom:1px solid #e5e7eb;">
                                <span style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280;">Producto</span>
                                <span style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280;">Cant. Sugerida</span>
                                <span style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280;">Cant. Real Recibida</span>
                            </div>
                        '));

                    foreach ($this->record->items as $i => $item) {
                        // Producto + cantidad sugerida como una sola fila HTML
                        $nombreEscapado = e($item->producto?->nombre ?? '—');
                        $unidadEscapada = e($item->producto?->unidad_medida ?? 'u');
                        $cantSugerida = number_format($item->cantidad_sugerida, 3);

                        $fields[] = Forms\Components\Placeholder::make("fila_{$i}")
                            ->label('')
                            ->content(new HtmlString("
                                <div style='display:grid;grid-template-columns:1fr 1fr;gap:16px;align-items:center;'>
                                    <span style='font-size:13px;font-weight:500;color:#1f2937;'>{$nombreEscapado}</span>
                                    <span style='font-size:13px;font-weight:600;color:#0d9488;'>
                                        {$cantSugerida}
                                        <span style='font-size:11px;font-weight:400;color:#9ca3af;margin-left:2px;'>{$unidadEscapada}</span>
                                    </span>
                                </div>
                            "));

                        $fields[] = Forms\Components\TextInput::make("items.{$i}.cantidad_real")
                            ->label('Cantidad recibida')
                            ->default($item->cantidad_sugerida)
                            ->disabled()
                            ->dehydrated()
                            ->suffix($item->producto?->unidad_medida ?? 'u');

                        // Separador entre productos
                        if ($i < $this->record->items->count() - 1) {
                            $fields[] = Forms\Components\Placeholder::make("sep_{$i}")
                                ->label('')
                                ->content(new HtmlString(
                                    '<div style="border-top:1px solid #f3f4f6;margin:4px 0;"></div>'
                                ));
                        }
                    }

                    return $fields;
                })
                ->action(function () {
                    // Confirma lo recibido; el ingreso al destino lo realiza TrasladoObserver.
                    foreach ($this->record->items as $item) {
                        $item->update(['cantidad_real' => floatval($item->cantidad_sugerida)]);
                    }

                    $this->record->update([
                        'estado' => 'completado',
                        'fecha_completado' => now(),
                    ]);

                    Notification::make()
                        ->success()
                        ->title('Traslado aceptado')
                        ->body('El inventario de tu sucursal ha sido actualizado.')
                        ->send();

                    $logistica = User::role(['logistica', 'supervisor_bodega', 'super_admin'])->get();
                    if ($logistica->isNotEmpty()) {
                        Notification::make()
                            ->title('Traslado recibido: '.$this->record->numero)
                            ->body('La sucursal destino confirmó la recepción del traslado.')
                            ->success()
                            ->sendToDatabase($logistica);
                    }

                    $this->record->refresh();
                }),

            Action::make('completar')
                ->label('Completar Traslado')
                ->icon('heroicon-m-check-circle')
                ->color('success')
                ->visible(fn () => $this->record->estado === 'en_transito')
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
                    // Solo persistimos la cantidad real; el ingreso al destino lo hace TrasladoObserver.
                    foreach ($data['items'] ?? [] as $itemData) {
                        $item = TrasladoItem::find($itemData['item_id']);
                        if (! $item) {
                            continue;
                        }
                        $item->update(['cantidad_real' => floatval($itemData['cantidad_real'])]);
                    }
                    $this->record->update(['estado' => 'completado', 'fecha_completado' => now()]);
                    Notification::make()->success()->title('Traslado completado. Inventario actualizado.')->send();
                    $this->record->refresh();
                }),

            Action::make('cancelar')
                ->label('Cancelar')
                ->icon('heroicon-m-x-circle')
                ->color('danger')
                ->visible(fn () => ! in_array($this->record->estado, ['completado', 'cancelado']))
                ->requiresConfirmation()
                ->modalHeading('¿Cancelar este traslado?')
                ->action(function () {
                    $this->record->update(['estado' => 'cancelado']);
                    Notification::make()->success()->title('Traslado cancelado')->send();
                    $this->record->refresh();
                }),

            EditAction::make()
                ->visible(fn () => $this->record->estado === 'sugerido'),
        ];
    }
}
