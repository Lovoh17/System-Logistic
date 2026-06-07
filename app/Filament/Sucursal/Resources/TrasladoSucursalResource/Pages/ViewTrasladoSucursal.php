<?php

namespace App\Filament\Sucursal\Resources\TrasladoSucursalResource\Pages;

use App\Filament\Sucursal\Resources\TrasladoSucursalResource;
use App\Models\InventarioAlmacen;
use App\Models\TrasladoItem;
use App\Models\User;
use Filament\Actions\Action;
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
            Action::make('aceptar_traslado')
                ->label('Aceptar y Recibir Traslado')
                ->icon('heroicon-m-inbox-arrow-down')
                ->color('success')
                ->visible(fn() =>
                    $this->record->estado === 'sugerido'
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
                        $nombreEscapado   = e($item->producto?->nombre ?? '—');
                        $unidadEscapada   = e($item->producto?->unidad_medida ?? 'u');
                        $cantSugerida     = number_format($item->cantidad_sugerida, 3);

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
                    $almacenDestinoId = $this->record->almacen_destino_id;
                    $almacenOrigenId  = $this->record->almacen_origen_id;

                    foreach ($this->record->items as $item) {
                        $cantReal = floatval($item->cantidad_sugerida);
                        $item->update(['cantidad_real' => $cantReal]);

                        $invDestino = InventarioAlmacen::firstOrCreate(
                            ['producto_id' => $item->producto_id, 'almacen_id' => $almacenDestinoId],
                            ['stock_actual' => 0, 'stock_minimo' => 0, 'stock_maximo' => 999999, 'punto_reorden' => 0]
                        );
                        $invDestino->increment('stock_actual', $cantReal);

                        $invOrigen = InventarioAlmacen::where('producto_id', $item->producto_id)
                            ->where('almacen_id', $almacenOrigenId)
                            ->first();

                        if ($invOrigen) {
                            $invOrigen->decrement('stock_actual', min($cantReal, $invOrigen->stock_actual));
                        }
                    }

                    $this->record->update([
                        'estado'           => 'completado',
                        'fecha_completado' => now(),
                        'aprobado_por'     => auth()->id(),
                        'fecha_aprobacion' => now(),
                    ]);

                    Notification::make()
                        ->success()
                        ->title('Traslado aceptado')
                        ->body('El inventario de tu sucursal ha sido actualizado.')
                        ->send();

                    $logistica = User::role(['logistica', 'supervisor_bodega', 'super_admin'])->get();
                    if ($logistica->isNotEmpty()) {
                        Notification::make()
                            ->title('Traslado recibido: ' . $this->record->numero)
                            ->body('La sucursal destino confirmó la recepción del traslado.')
                            ->success()
                            ->sendToDatabase($logistica);
                    }

                    $this->record->refresh();
                }),
        ];
    }
}