<?php

namespace App\Filament\Contador\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DeclaracionIva extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';

    protected static ?string $navigationLabel = 'Declaración IVA';

    protected static ?string $title = 'Declaración de IVA Mensual';

    protected static ?string $navigationGroup = 'Reportes';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.contador.pages.declaracion-iva';

    public ?array $data = [];

    public float $debitoFiscal = 0;

    public float $creditoFiscal = 0;

    public float $ivaNetoPagar = 0;

    public float $ivaFavorContribuyente = 0;

    public string $periodo = '';

    public string $desde = '';

    public string $hasta = '';

    // Detalle por asiento
    public array $detalleDebito = [];

    public array $detalleCredito = [];

    public function mount(): void
    {
        $this->form->fill([
            'desde' => now()->startOfMonth()->toDateString(),
            'hasta' => now()->endOfMonth()->toDateString(),
        ]);
        $this->calcular();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Período de Declaración')
                    ->icon('heroicon-o-calendar-days')
                    ->columns(2)
                    ->schema([
                        DatePicker::make('desde')
                            ->label('Desde')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y'),

                        DatePicker::make('hasta')
                            ->label('Hasta')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->afterOrEqual('desde'),
                    ]),
            ])
            ->statePath('data');
    }

    public function calcular(): void
    {
        $this->desde = $this->data['desde'] ?? now()->startOfMonth()->toDateString();
        $this->hasta = $this->data['hasta'] ?? now()->endOfMonth()->toDateString();

        $meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        $mes = (int) date('m', strtotime($this->desde));
        $anio = date('Y', strtotime($this->desde));
        $this->periodo = $meses[$mes].' '.$anio;

        $desde = $this->desde;
        $hasta = $this->hasta;

        $datos = Cache::remember("declaracion_iva:{$desde}:{$hasta}", now()->addMinutes(10), function () use ($desde, $hasta) {
            // Débito Fiscal = saldo de cuenta 2.1.02 (IVA Débito Fiscal — naturaleza acreedora)
            // En partida doble: las ventas generan HABER en 2.1.02, por tanto saldo = haber - debe
            $rowDebito = DB::table('lineas_asiento as la')
                ->join('asientos_contables as ac', 'ac.id', '=', 'la.asiento_contable_id')
                ->join('cuentas_contables as cc', 'cc.id', '=', 'la.cuenta_contable_id')
                ->where('ac.estado', 'registrado')
                ->whereBetween(DB::raw('DATE(ac.fecha)'), [$desde, $hasta])
                ->where('cc.codigo', '2.1.02')
                ->selectRaw('SUM(la.haber) as haber, SUM(la.debe) as debe')
                ->first();

            // Crédito Fiscal = saldo de cuenta 1.1.04 (IVA Crédito Fiscal — naturaleza deudora)
            // Las compras generan DEBE en 1.1.04, por tanto saldo = debe - haber
            $rowCredito = DB::table('lineas_asiento as la')
                ->join('asientos_contables as ac', 'ac.id', '=', 'la.asiento_contable_id')
                ->join('cuentas_contables as cc', 'cc.id', '=', 'la.cuenta_contable_id')
                ->where('ac.estado', 'registrado')
                ->whereBetween(DB::raw('DATE(ac.fecha)'), [$desde, $hasta])
                ->where('cc.codigo', '1.1.04')
                ->selectRaw('SUM(la.debe) as debe, SUM(la.haber) as haber')
                ->first();

            return [
                'debitoFiscal' => $rowDebito
                    ? max(0, (float) $rowDebito->haber - (float) $rowDebito->debe)
                    : 0.0,
                'creditoFiscal' => $rowCredito
                    ? max(0, (float) $rowCredito->debe - (float) $rowCredito->haber)
                    : 0.0,
                'detalleDebito' => DB::table('lineas_asiento as la')
                    ->join('asientos_contables as ac', 'ac.id', '=', 'la.asiento_contable_id')
                    ->join('cuentas_contables as cc', 'cc.id', '=', 'la.cuenta_contable_id')
                    ->where('ac.estado', 'registrado')
                    ->whereBetween(DB::raw('DATE(ac.fecha)'), [$desde, $hasta])
                    ->where('cc.codigo', '2.1.02')
                    ->where('la.haber', '>', 0)
                    ->select(['ac.fecha', 'ac.numero', 'ac.numero_documento', 'ac.tipo_documento', 'la.haber as monto'])
                    ->orderBy('ac.fecha')
                    ->get()
                    ->toArray(),
                'detalleCredito' => DB::table('lineas_asiento as la')
                    ->join('asientos_contables as ac', 'ac.id', '=', 'la.asiento_contable_id')
                    ->join('cuentas_contables as cc', 'cc.id', '=', 'la.cuenta_contable_id')
                    ->where('ac.estado', 'registrado')
                    ->whereBetween(DB::raw('DATE(ac.fecha)'), [$desde, $hasta])
                    ->where('cc.codigo', '1.1.04')
                    ->where('la.debe', '>', 0)
                    ->select(['ac.fecha', 'ac.numero', 'ac.numero_documento', 'ac.tipo_documento', 'la.debe as monto'])
                    ->orderBy('ac.fecha')
                    ->get()
                    ->toArray(),
            ];
        });

        $this->debitoFiscal = $datos['debitoFiscal'];
        $this->creditoFiscal = $datos['creditoFiscal'];

        $diferencia = $this->debitoFiscal - $this->creditoFiscal;
        $this->ivaNetoPagar = max(0, $diferencia);
        $this->ivaFavorContribuyente = max(0, -$diferencia);

        $this->detalleDebito = $datos['detalleDebito'];
        $this->detalleCredito = $datos['detalleCredito'];
    }

    public function recalcular(): void
    {
        $desde = $this->data['desde'] ?? now()->startOfMonth()->toDateString();
        $hasta = $this->data['hasta'] ?? now()->endOfMonth()->toDateString();
        Cache::forget("declaracion_iva:{$desde}:{$hasta}");
        $this->calcular();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('calcular')
                ->label('Recalcular')
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->action('recalcular'),
        ];
    }
}
