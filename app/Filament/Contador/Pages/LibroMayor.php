<?php

namespace App\Filament\Contador\Pages;

use App\Models\CuentaContable;
use App\Models\LineaAsiento;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;

class LibroMayor extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon  = 'heroicon-o-table-cells';
    protected static ?string $navigationLabel = 'Libro Mayor';
    protected static ?string $title           = 'Libro Mayor';
    protected static ?string $navigationGroup = 'Contabilidad';
    protected static ?int    $navigationSort  = 2;
    protected static string  $view            = 'filament.contador.pages.libro-mayor';

    public ?array $data            = [];
    public array  $movimientos     = [];
    public float  $saldo_inicial   = 0;
    public float  $saldo_acumulado = 0;
    public ?array $cuenta          = null;
    public bool   $consultado      = false;

    public function mount(): void
    {
        $this->form->fill([
            'cuenta_contable_id' => null,
            'desde'              => now()->startOfMonth()->toDateString(),
            'hasta'              => now()->toDateString(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Consultar Cuenta')
                    ->icon('heroicon-o-magnifying-glass')
                    ->columns(3)
                    ->schema([
                        Select::make('cuenta_contable_id')
                            ->label('Cuenta Contable')
                            ->options(
                                CuentaContable::movibles()
                                    ->orderBy('codigo')
                                    ->get()
                                    ->mapWithKeys(fn ($c) => [$c->id => "{$c->codigo} — {$c->nombre}"])
                            )
                            ->searchable()
                            ->required()
                            ->columnSpan(1),

                        DatePicker::make('desde')
                            ->label('Desde')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->columnSpan(1),

                        DatePicker::make('hasta')
                            ->label('Hasta')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->afterOrEqual('desde')
                            ->columnSpan(1),
                    ]),
            ])
            ->statePath('data');
    }

    public function consultar(): void
    {
        $this->form->validate();

        $cuentaId = $this->data['cuenta_contable_id'];
        $desde    = $this->data['desde'];
        $hasta    = $this->data['hasta'];

        $cuentaModel = CuentaContable::findOrFail($cuentaId);

        $this->cuenta = [
            'codigo'     => $cuentaModel->codigo,
            'nombre'     => $cuentaModel->nombre,
            'naturaleza' => $cuentaModel->naturaleza,
            'tipo'       => $cuentaModel->tipo_label,
        ];

        // Saldo acumulado ANTES del período
        $debeAntes  = (float) LineaAsiento::where('cuenta_contable_id', $cuentaId)
            ->whereHas('asiento', fn ($q) => $q->where('estado', 'registrado')
                ->whereDate('fecha', '<', $desde))
            ->sum('debe');
        $haberAntes = (float) LineaAsiento::where('cuenta_contable_id', $cuentaId)
            ->whereHas('asiento', fn ($q) => $q->where('estado', 'registrado')
                ->whereDate('fecha', '<', $desde))
            ->sum('haber');

        $this->saldo_inicial = $cuentaModel->naturaleza === 'deudora'
            ? $debeAntes - $haberAntes
            : $haberAntes - $debeAntes;

        // Movimientos del período
        $lineas = LineaAsiento::with(['asiento'])
            ->where('cuenta_contable_id', $cuentaId)
            ->whereHas('asiento', fn ($q) => $q->where('estado', 'registrado')
                ->whereBetween('fecha', [$desde, $hasta]))
            ->get()
            ->sortBy('asiento.fecha');

        $saldo            = $this->saldo_inicial;
        $this->movimientos = [];

        foreach ($lineas as $linea) {
            $debe  = (float) $linea->debe;
            $haber = (float) $linea->haber;

            $saldo += $cuentaModel->naturaleza === 'deudora'
                ? ($debe - $haber)
                : ($haber - $debe);

            $this->movimientos[] = [
                'fecha'          => $linea->asiento->fecha->format('d/m/Y'),
                'numero_asiento' => $linea->asiento->numero,
                'descripcion'    => $linea->descripcion ?: $linea->asiento->descripcion,
                'debe'           => $debe,
                'haber'          => $haber,
                'saldo'          => $saldo,
            ];
        }

        $this->saldo_acumulado = $saldo;
        $this->consultado      = true;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('consultar')
                ->label('Consultar')
                ->icon('heroicon-o-magnifying-glass')
                ->color('primary')
                ->action('consultar'),
        ];
    }
}
