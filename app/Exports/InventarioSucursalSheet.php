<?php

namespace App\Exports;

use App\Models\InventarioAlmacen;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InventarioSucursalSheet implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithTitle, WithStyles
{
    protected $sucursal;

    public function __construct($sucursal)
    {
        $this->sucursal = $sucursal;
    }

    public function collection()
    {
        return InventarioAlmacen::with(['producto'])
            ->where('almacen_id', $this->sucursal->id)
            ->get();
    }

    public function headings(): array
    {
        return [
            'Código Producto',
            'Producto',
            'Stock Actual',
            'Stock Mínimo',
            'Stock Máximo',
            'Diferencia',
            'Estado',
            'Fecha Actualización',
        ];
    }

    public function map($item): array
    {
        $diferencia = '';
        $estado = '';
        
        if ($item->stock_actual > $item->stock_maximo) {
            $diferencia = '+' . number_format($item->stock_actual - $item->stock_maximo, 2) . ' (sobra)';
            $estado = 'SOBRESTOCK';
        } elseif ($item->stock_actual < $item->stock_minimo) {
            $diferencia = '-' . number_format($item->stock_minimo - $item->stock_actual, 2) . ' (falta)';
            $estado = 'STOCK BAJO';
        } else {
            $diferencia = '✓ OK';
            $estado = 'Óptimo';
        }
        
        return [
            $item->producto?->codigo ?? 'N/A',
            $item->producto?->nombre ?? 'N/A',
            number_format($item->stock_actual, 2),
            number_format($item->stock_minimo, 2),
            number_format($item->stock_maximo, 2),
            $diferencia,
            $estado,
            $item->updated_at?->format('d/m/Y H:i') ?? 'N/A',
        ];
    }

    public function title(): string
    {
        // Limitar título a 31 caracteres (máximo de Excel)
        return substr($this->sucursal->nombre, 0, 31);
    }

    public function styles(Worksheet $sheet)
    {
        // Estilo para encabezados
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'font' => ['color' => ['rgb' => 'FFFFFF']],
        ]);
        
        // Colorear filas según estado
        $rows = $this->collection()->count() + 1;
        for ($i = 2; $i <= $rows; $i++) {
            $estado = $sheet->getCell("G{$i}")->getValue();
            if ($estado === 'SOBRESTOCK') {
                $sheet->getStyle("A{$i}:H{$i}")->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFEB9C'],
                    ],
                ]);
            } elseif ($estado === 'STOCK BAJO') {
                $sheet->getStyle("A{$i}:H{$i}")->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFC7CE'],
                    ],
                ]);
            } else {
                $sheet->getStyle("A{$i}:H{$i}")->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'C6EFCE'],
                    ],
                ]);
            }
        }
        
        return [];
    }
}