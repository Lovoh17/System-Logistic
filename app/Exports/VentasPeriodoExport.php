<?php

namespace App\Exports;

use App\Models\PedidoVenta;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class VentasPeriodoExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    public function __construct(
        protected string $fechaInicio,
        protected string $fechaFin
    ) {}

    public function query()
    {
        return PedidoVenta::query()
            ->with(['cliente', 'almacen', 'user'])
            ->whereDate('fecha_pedido', '>=', $this->fechaInicio)
            ->whereDate('fecha_pedido', '<=', $this->fechaFin)
            ->whereNotIn('estado', ['borrador'])
            ->orderBy('fecha_pedido');
    }

    public function title(): string
    {
        return 'Ventas ' . $this->fechaInicio . ' al ' . $this->fechaFin;
    }

    public function headings(): array
    {
        return [
            'N° Pedido',
            'Fecha',
            'Cliente',
            'Sucursal',
            'Vendedor',
            'Estado',
            'Canal Venta',
            'Subtotal ($)',
            'IVA ($)',
            'Descuento ($)',
            'Total ($)',
        ];
    }

    public function map($pedido): array
    {
        return [
            $pedido->numero,
            $pedido->fecha_pedido?->format('d/m/Y'),
            $pedido->cliente?->nombre ?? 'N/A',
            $pedido->almacen?->nombre ?? 'N/A',
            $pedido->user?->name ?? 'N/A',
            strtoupper($pedido->estado),
            $pedido->canal_venta ?? 'N/A',
            number_format((float) $pedido->subtotal, 2),
            number_format((float) $pedido->impuesto, 2),
            number_format((float) $pedido->descuento, 2),
            number_format((float) $pedido->total, 2),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $sheet->getHighestRow();

        // Total row
        $sheet->setCellValue("A{$lastRow}", 'TOTAL');
        $totalCol = 'K';

        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '059669']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }
}