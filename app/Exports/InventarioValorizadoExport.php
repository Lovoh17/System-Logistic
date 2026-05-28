<?php

namespace App\Exports;

use App\Models\Almacen;
use App\Models\InventarioAlmacen;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InventarioValorizadoExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        $sheets = [new InventarioValorizadoResumenSheet()];

        Almacen::where('activo', true)->orderBy('nombre')->each(function ($almacen) use (&$sheets) {
            $sheets[] = new InventarioValorizadoAlmacenSheet($almacen);
        });

        return $sheets;
    }
}

// ─── Hoja resumen general ─────────────────────────────────────────────────────
class InventarioValorizadoResumenSheet implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    public function title(): string { return 'Resumen General'; }

    public function collection()
    {
        return InventarioAlmacen::query()
            ->with(['producto.categoria', 'almacen'])
            ->get()
            ->groupBy('producto_id')
            ->map(function ($registros) {
                $primer = $registros->first();
                return (object) [
                    'producto'       => $primer->producto,
                    'stock_total'    => $registros->sum('stock_actual'),
                    'precio_compra'  => $primer->producto->precio_compra,
                    'precio_venta'   => $primer->producto->precio_venta,
                    'valor_costo'    => $registros->sum('stock_actual') * $primer->producto->precio_compra,
                    'valor_venta'    => $registros->sum('stock_actual') * $primer->producto->precio_venta,
                ];
            })
            ->sortByDesc('valor_costo')
            ->values();
    }

    public function headings(): array
    {
        return ['Código', 'Producto', 'Categoría', 'Stock Total', 'P. Compra ($)', 'P. Venta ($)', 'Valor Costo ($)', 'Valor Venta ($)'];
    }

    public function map($row): array
    {
        return [
            $row->producto->codigo,
            $row->producto->nombre,
            $row->producto->categoria?->nombre ?? 'N/A',
            $row->stock_total,
            number_format((float) $row->precio_compra, 2),
            number_format((float) $row->precio_venta, 2),
            number_format((float) $row->valor_costo, 2),
            number_format((float) $row->valor_venta, 2),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '059669']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }
}

// ─── Hoja por almacén ─────────────────────────────────────────────────────────
class InventarioValorizadoAlmacenSheet implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    public function __construct(protected Almacen $almacen) {}

    public function title(): string
    {
        return mb_strimwidth($this->almacen->nombre, 0, 30, '');
    }

    public function collection()
    {
        return InventarioAlmacen::query()
            ->with(['producto.categoria'])
            ->where('almacen_id', $this->almacen->id)
            ->where('stock_actual', '>', 0)
            ->orderByDesc('stock_actual')
            ->get();
    }

    public function headings(): array
    {
        return ['Código', 'Producto', 'Categoría', 'Stock Actual', 'Stock Mín.', 'P. Compra ($)', 'Valor Costo ($)', 'P. Venta ($)', 'Valor Venta ($)', 'Estado Stock'];
    }

    public function map($inv): array
    {
        $valorCosto = $inv->stock_actual * $inv->producto->precio_compra;
        $valorVenta = $inv->stock_actual * $inv->producto->precio_venta;
        $estadoStock = $inv->stock_actual <= $inv->stock_minimo ? 'CRÍTICO' : ($inv->stock_actual <= ($inv->stock_minimo * 1.2) ? 'BAJO' : 'OK');

        return [
            $inv->producto->codigo,
            $inv->producto->nombre,
            $inv->producto->categoria?->nombre ?? 'N/A',
            $inv->stock_actual,
            $inv->stock_minimo,
            number_format((float) $inv->producto->precio_compra, 2),
            number_format((float) $valorCosto, 2),
            number_format((float) $inv->producto->precio_venta, 2),
            number_format((float) $valorVenta, 2),
            $estadoStock,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0f766e']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }
}