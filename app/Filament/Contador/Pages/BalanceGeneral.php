<?php

namespace App\Filament\Contador\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class BalanceGeneral extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon  = 'heroicon-o-scale';
    protected static ?string $navigationLabel = 'Balance General';
    protected static ?string $title           = 'Balance General';
    protected static ?string $navigationGroup = 'Finanzas';
    protected static ?int    $navigationSort  = 1;
    protected static string  $view            = 'filament.contador.pages.balance-general';

    public ?array $data = [];

    public array $activos         = [];
    public array $pasivos         = [];
    public array $capital         = [];
    public float $totalActivos    = 0;
    public float $totalPasivos    = 0;
    public float $totalCapital    = 0;
    public float $totalPasivoCapital = 0;
    public bool  $balanceado      = false;
    public string $hasta          = '';

    public function mount(): void
    {
        $this->form->fill(['hasta' => now()->toDateString()]);
        $this->calcular();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Fecha de Balance')
                    ->icon('heroicon-o-calendar-days')
                    ->columns(1)
                    ->schema([
                        DatePicker::make('hasta')
                            ->label('Al día')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->default(today()),
                    ]),
            ])
            ->statePath('data');
    }

    public function calcular(): void
    {
        $hasta       = $this->data['hasta'] ?? now()->toDateString();
        $this->hasta = $hasta;

        $this->activos = $this->pasivos = $this->capital = [];
        $this->totalActivos = $this->totalPasivos = $this->totalCapital = 0;

        $saldos = DB::table('lineas_asiento as la')
            ->join('asientos_contables as ac', 'ac.id', '=', 'la.asiento_contable_id')
            ->join('cuentas_contables as cc', 'cc.id', '=', 'la.cuenta_contable_id')
            ->where('ac.estado', 'registrado')
            ->whereDate('ac.fecha', '<=', $hasta)
            ->where('cc.acepta_movimientos', true)
            ->where('cc.activa', true)
            ->whereIn('cc.tipo', ['activo', 'pasivo', 'capital'])
            ->groupBy('la.cuenta_contable_id', 'cc.codigo', 'cc.nombre', 'cc.tipo', 'cc.naturaleza')
            ->select([
                DB::raw('MAX(cc.codigo) as codigo'),
                DB::raw('MAX(cc.nombre) as nombre'),
                DB::raw('MAX(cc.tipo) as tipo'),
                DB::raw('MAX(cc.naturaleza) as naturaleza'),
                DB::raw('SUM(la.debe) as total_debe'),
                DB::raw('SUM(la.haber) as total_haber'),
            ])
            ->orderBy('codigo')
            ->get();

        foreach ($saldos as $row) {
            $saldo = $row->naturaleza === 'deudora'
                ? (float) $row->total_debe - (float) $row->total_haber
                : (float) $row->total_haber - (float) $row->total_debe;

            if (abs($saldo) < 0.005) continue;

            $item = ['codigo' => $row->codigo, 'nombre' => $row->nombre, 'saldo' => $saldo];

            if ($row->tipo === 'activo') {
                $this->activos[]    = $item;
                $this->totalActivos += $saldo;
            } elseif ($row->tipo === 'pasivo') {
                $this->pasivos[]    = $item;
                $this->totalPasivos += $saldo;
            } else {
                $this->capital[]    = $item;
                $this->totalCapital += $saldo;
            }
        }

        $this->totalPasivoCapital = $this->totalPasivos + $this->totalCapital;
        $this->balanceado         = abs($this->totalActivos - $this->totalPasivoCapital) < 0.01;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('calcular')
                ->label('Recalcular')
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->action('calcular'),
        ];
    }
}
