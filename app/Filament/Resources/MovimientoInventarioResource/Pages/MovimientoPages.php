<?php
// ════════════════════════════════════════════════════
// PAGES — MovimientoInventarioResource
// ════════════════════════════════════════════════════
namespace App\Filament\Resources\MovimientoInventarioResource\Pages;

use App\Filament\Resources\MovimientoInventarioResource;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class ListMovimientosInventario extends ListRecords
{
    protected static string $resource = MovimientoInventarioResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()->label('Ajuste Manual')];
    }
}

class CreateMovimientoInventario extends CreateRecord
{
    protected static string $resource = MovimientoInventarioResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['numero']  = MovimientoInventario::generarNumero();
        $data['user_id'] = auth()->id();

        $producto            = Producto::findOrFail($data['producto_id']);
        $data['stock_anterior'] = $producto->stock_actual;

        $esEntrada = in_array($data['tipo'], [
            'ajuste_positivo', 'inventario_inicial', 'traslado_entrada',
        ]);

        $data['stock_nuevo'] = $esEntrada
            ? $producto->stock_actual + $data['cantidad']
            : max(0, $producto->stock_actual - $data['cantidad']);

        if (isset($data['costo_unitario'])) {
            $data['costo_total'] = $data['cantidad'] * $data['costo_unitario'];
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        // Actualizar el stock del producto al guardar el movimiento
        $movimiento = $this->record;
        $movimiento->producto->update(['stock_actual' => $movimiento->stock_nuevo]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
