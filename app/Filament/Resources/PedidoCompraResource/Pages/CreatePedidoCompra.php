<?php

namespace App\Filament\Resources\PedidoCompraResource\Pages;

use App\Filament\Resources\PedidoCompraResource;
use Filament\Resources\Pages\CreateRecord;

use Illuminate\Support\Facades\Log;

class CreatePedidoCompra extends CreateRecord
{
    protected static string $resource = PedidoCompraResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        $items = $this->form->getRawState()['items'] ?? [];
        
        $subtotal = collect($items)->sum(fn($item) => floatval($item['subtotal'] ?? 0));
        $impuesto  = floatval($data['impuesto']  ?? 0);
        $descuento = floatval($data['descuento'] ?? 0);
        $total     = round($subtotal + $impuesto - $descuento, 2);

        $data['subtotal'] = round($subtotal, 2);
        $data['total']    = $total;

        Log::info('[OC] mutateFormDataBeforeCreate', [
            'user_id'  => $data['user_id'],
            'items'    => count($items),
            'subtotal' => $subtotal,
            'total'    => $total,
        ]);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
