<?php

namespace App\Filament\Resources\PedidoCompraResource\Pages;

use App\Filament\Resources\PedidoCompraResource;
use App\Models\Producto;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class CreatePedidoCompra extends CreateRecord
{
    protected static string $resource = PedidoCompraResource::class;

    public function mount(): void
    {
        parent::mount();

        $productoId        = (int)   request()->query('producto_id',        0);
        $cantidadNecesaria = (float) request()->query('cantidad_necesaria', 1);
        $proveedorId       = (int)   request()->query('proveedor_id',       0);
        $fromSession       = (bool)  request()->query('from_session',       0);

        $rawItems = [];

        if ($fromSession && session()->has('oc_items_proveedor')) {
            $rawItems = session()->pull('oc_items_proveedor');
        } elseif ($productoId) {
            $producto = Producto::find($productoId);
            if ($producto) {
                $cantidad = max(1, (int) ceil($cantidadNecesaria));
                $rawItems = [[
                    'producto_id'     => $producto->id,
                    'cantidad'        => $cantidad,
                    'precio_unitario' => (float) $producto->precio_compra,
                    'unidad_medida'   => $producto->unidad_medida,
                    'descuento'       => 0,
                    'subtotal'        => round($cantidad * (float) $producto->precio_compra, 2),
                ]];
            }
        }

        if (empty($rawItems)) return;

        $itemsKeyed = collect($rawItems)
            ->mapWithKeys(fn($item) => [(string) Str::uuid() => $item])
            ->toArray();

        $subtotal = collect($rawItems)->sum(fn($i) => floatval($i['subtotal'] ?? 0));

        $this->form->fill([
            'proveedor_id' => $proveedorId ?: null,
            'items'        => $itemsKeyed,
            'subtotal'     => round($subtotal, 2),
            'impuesto'     => 0,
            'descuento'    => 0,
            'total'        => round($subtotal, 2),
        ]);
    }

    /**
     * Botón en el header de la página que recalcula subtotal y total
     * leyendo el estado actual del repeater de items.
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('recalcular_totales')
                ->label('Recalcular Total')
                ->icon('heroicon-o-calculator')
                ->color('gray')
                ->action(function () {
                    $state     = $this->form->getRawState();
                    $items     = $state['items'] ?? [];
                    $subtotal  = collect($items)->sum(fn($i) => floatval($i['subtotal'] ?? 0));
                    $impuesto  = floatval($state['impuesto']  ?? 0);
                    $descuento = floatval($state['descuento'] ?? 0);

                    $this->form->fill(array_merge($state, [
                        'subtotal' => round($subtotal, 2),
                        'total'    => round($subtotal + $impuesto - $descuento, 2),
                    ]));
                }),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        $items     = $this->form->getRawState()['items'] ?? [];
        $subtotal  = collect($items)->sum(fn($item) => floatval($item['subtotal'] ?? 0));
        $impuesto  = floatval($data['impuesto']  ?? 0);
        $descuento = floatval($data['descuento'] ?? 0);

        $data['subtotal'] = round($subtotal, 2);
        $data['total']    = round($subtotal + $impuesto - $descuento, 2);

        Log::info('[OC] mutateFormDataBeforeCreate', [
            'user_id'  => $data['user_id'],
            'items'    => count($items),
            'subtotal' => $data['subtotal'],
            'total'    => $data['total'],
        ]);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}