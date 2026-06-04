<?php

namespace App\Exports;

use App\Models\Cliente;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CuentasPorCobrarExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    public function title(): string
    {
        return 'Cuentas por Cobrar ' . now()->format('d-m-Y');
    }

    public function collection()
    {
        return Cliente::query()
            ->where('limite_credito', '>', 0)
            ->withCount([
                'pedidosVenta as pedidos_pendientes_count' => fn ($q) =>
                    $q->whereNotIn('estado', ['entregado', 'cancelado', 'borrador']),
            ])
            ->withSum([
                'pedidosVenta as monto_pendiente' => fn ($q) =>
                    $q->whereNotIn('estado', ['entregado', 'cancelado', 'borrador']),
            ], 'total')
            ->orderByDesc('monto_pendiente')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Código',
            'Cliente',
            'Tipo',
            'NIT',
            'Teléfono',
            'Email',
            'Límite Crédito ($)',
            'Días Crédito',
            'Pedidos Pendientes',
            'Monto Pendiente ($)',
            '% Utilizado',
        ];
    }

    public function map($cliente): array
    {
        $montoPendiente   = (float) ($cliente->monto_pendiente ?? 0);
        $limiteCredito    = (float) $cliente->limite_credito;
        $porcentajeUso    = $limiteCredito > 0 ? round(($montoPendiente / $limiteCredito) * 100, 1) : 0;

        return [
            $cliente->codigo,
            $cliente->nombre,
            ucfirst($cliente->tipo),
            $cliente->nit ?? 'N/A',
            $cliente->telefono ?? 'N/A',
            $cliente->email ?? 'N/A',
            number_format($limiteCredito, 2),
            $cliente->dias_credito ?? 0,
            $cliente->pedidos_pendientes_count,
            number_format($montoPendiente, 2),
            $porcentajeUso . '%',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'b45309']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }
}