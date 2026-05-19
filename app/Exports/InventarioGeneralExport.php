<?php

namespace App\Exports;

use App\Models\Producto;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InventarioGeneralExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $productos;

    public function __construct($productos = null)
    {
        $this->productos = $productos;
    }

    public function collection()
    {
        if ($this->productos) {
            return $this->productos;
        }
        return Producto::with(['categoria', 'proveedor'])->get();
    }

    public function headings(): array
    {
        return [
            'Código',
            'SKU',
            'Producto',
            'Categoría',
            'Proveedor',
            'Stock Actual',
            'Stock Mínimo',
            'Stock Máximo',
            'Precio Compra',
            'Precio Venta',
            'Ubicación',
            'Estado',
        ];
    }

    public function map($producto): array
    {
        return [
            $producto->codigo,
            $producto->sku,
            $producto->nombre,
            $producto->categoria?->nombre ?? 'N/A',
            $producto->proveedor?->nombre ?? 'N/A',
            $producto->stock_actual,
            $producto->stock_minimo,
            $producto->stock_maximo,
            $producto->precio_compra,
            $producto->precio_venta,
            $producto->ubicacion_almacen,
            $producto->estado,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}