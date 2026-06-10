<?php

namespace App\Filament\Resources\ProductoResource\Pages;

use App\Filament\Resources\ProductoResource;
use App\Models\Producto;
use Filament\Actions;
use Filament\Resources\Pages\Page;

class ViewStockProducto extends Page
{
    protected static string $resource = ProductoResource::class;
    protected static string $view     = 'filament.resources.producto-resource.pages.view-stock-producto';

    public Producto $record;

    public function mount(Producto $record): void
    {
        $this->record = $record->load(['inventarioAlmacen.almacen', 'categoria']);

        abort_unless(static::getResource()::canView($this->record), 403);
    }

    public function getTitle(): string
    {
        return "Stock: {$this->record->nombre}";
    }

    public function getBreadcrumbs(): array
    {
        return [
            ProductoResource::getUrl()            => 'Productos',
            ProductoResource::getUrl('view', ['record' => $this->record]) => $this->record->nombre,
            ''                                    => 'Stock',
        ];
    }

    public function getStockData()
    {
        return $this->record->inventarioAlmacen;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('ver_producto')
                ->label('Ver Producto')
                ->url(ProductoResource::getUrl('view', ['record' => $this->record]))
                ->icon('heroicon-m-eye')
                ->color('gray'),
            Actions\Action::make('editar')
                ->label('Editar')
                ->url(ProductoResource::getUrl('edit', ['record' => $this->record]))
                ->icon('heroicon-m-pencil-square')
                ->color('primary'),
        ];
    }
}
