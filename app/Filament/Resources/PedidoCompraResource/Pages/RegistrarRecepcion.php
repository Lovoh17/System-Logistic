<?php

namespace App\Filament\Resources\PedidoCompraResource\Pages;

use App\Filament\Resources\PedidoCompraResource;
use App\Models\Almacen;
use App\Models\InventarioAlmacen;
use App\Models\MovimientoInventario;
use App\Models\PedidoCompra;
use App\Models\PedidoCompraItem;
use App\Models\Traslado;
use App\Models\TrasladoItem;
use App\Models\Transportista;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class RegistrarRecepcion extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = PedidoCompraResource::class;
    protected static string $view     = 'filament.resources.pedido-compra-resource.pages.registrar-recepcion';

    public PedidoCompra $record;
    public ?array $data = [];

    public function mount(PedidoCompra $record): void
    {
        $this->record = $record->load(['items.producto']);

        abort_unless(
            in_array($this->record->estado, ['enviado', 'confirmado', 'parcial']),
            403,
            'Este pedido no puede recibirse en su estado actual.'
        );

        $this->form->fill([
            'fecha_recepcion'   => today()->format('Y-m-d'),
            'almacen_id'        => Almacen::where('es_principal', true)->value('id')
                ?? Almacen::activo()->value('id'),
            'lote'              => '',
            'fecha_vencimiento' => null,
            'notas'             => '',
            'items_recepcion'   => $this->record->items
                ->filter(fn($item) => $item->cantidad_pendiente > 0.001)
                ->map(fn($item) => [
                    'item_id'                   => $item->id,
                    'producto_nombre'            => $item->producto->nombre ?? 'Producto',
                    'cantidad_pendiente_display' => number_format($item->cantidad_pendiente, 3, '.', ''),
                    'cantidad_a_recibir'         => $item->cantidad_pendiente,
                    'nota_recepcion'             => '',
                ])->values()->toArray(),
        ]);
    }

    public function getTitle(): string|Htmlable
    {
        return "Registrar Recepción — {$this->record->numero}";
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Datos de Recepción')
                    ->icon('heroicon-o-truck')
                    ->columns(4)
                    ->schema([
                        Forms\Components\DatePicker::make('fecha_recepcion')
                            ->label('Fecha de Recepción')
                            ->required(),

                        Forms\Components\Select::make('almacen_id')
                            ->label('Almacén de Destino')
                            ->options(fn() => Almacen::activo()->pluck('nombre', 'id'))
                            ->searchable()
                            ->required(),

                        Forms\Components\TextInput::make('lote')
                            ->label('N° de Lote (opcional)')
                            ->maxLength(50),

                        Forms\Components\DatePicker::make('fecha_vencimiento')
                            ->label('Vencimiento (opcional)')
                            ->minDate(today()),
                    ]),

                Forms\Components\Section::make('Ítems a Recibir')
                    ->description('Solo se muestran ítems con cantidad pendiente. Los ya completados no aparecen.')
                    ->icon('heroicon-o-list-bullet')
                    ->schema([
                        Forms\Components\Repeater::make('items_recepcion')
                            ->label('')
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->columns(5)
                            ->schema([
                                Forms\Components\Hidden::make('item_id'),

                                Forms\Components\TextInput::make('producto_nombre')
                                    ->label('Producto')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('cantidad_pendiente_display')
                                    ->label('Pendiente')
                                    ->disabled()
                                    ->dehydrated(false),

                                Forms\Components\TextInput::make('cantidad_a_recibir')
                                    ->label('A Recibir')
                                    ->numeric()
                                    ->minValue(0)
                                    ->required(),

                                Forms\Components\TextInput::make('nota_recepcion')
                                    ->label('Observación (opcional)')
                                    ->maxLength(200)
                                    ->placeholder('Daños, diferencias de unidad, etc.')
                                    ->columnSpan(5),
                            ]),
                    ]),

                Forms\Components\Section::make('Notas Generales')
                    ->collapsed()
                    ->schema([
                        Forms\Components\Textarea::make('notas')
                            ->label('Notas de Recepción')
                            ->rows(3),
                    ]),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        if ($this->record->fecha_pedido && $data['fecha_recepcion'] < $this->record->fecha_pedido->format('Y-m-d')) {
            Notification::make()->danger()
                ->title('Fecha inválida')
                ->body('La fecha de recepción no puede ser anterior a la fecha del pedido.')
                ->send();
            return;
        }

        $userId             = auth()->id();
        $almacenId          = $data['almacen_id'];
        $lote               = $data['lote'] ?? null;
        $vencimiento        = $data['fecha_vencimiento'] ?? null;
        $todoRecibido       = true;
        $alertasStock       = [];
        $trasladosSugeridos = [];

        foreach ($data['items_recepcion'] as $itemData) {
            $item = PedidoCompraItem::find($itemData['item_id']);
            if (!$item) continue;

            $cantARecibir = floatval($itemData['cantidad_a_recibir']);
            $pendiente    = floatval($item->cantidad_pendiente);
            $cantARecibir = min($cantARecibir, $pendiente);

            if ($cantARecibir <= 0) {
                if ($pendiente > 0) $todoRecibido = false;
                continue;
            }
            $item->update([
                'cantidad_recibida' => floatval($item->cantidad_recibida) + $cantARecibir,
            ]);

            if (floatval($item->fresh()->cantidad_recibida) < floatval($item->cantidad)) {
                $todoRecibido = false;
            }
            $inventario = InventarioAlmacen::firstOrCreate(
                ['producto_id' => $item->producto_id, 'almacen_id' => $almacenId],
                ['stock_actual' => 0, 'stock_minimo' => 0, 'stock_maximo' => 9999, 'punto_reorden' => 0]
            );
            $stockAnterior = floatval($inventario->stock_actual);
            $stockNuevo    = $stockAnterior + $cantARecibir;
            $inventario->update(['stock_actual' => $stockNuevo]);
            MovimientoInventario::create([
                'numero'            => MovimientoInventario::generarNumero(),
                'producto_id'       => $item->producto_id,
                'almacen_id'        => $almacenId,
                'user_id'           => $userId,
                'tipo'              => 'entrada_compra',
                'cantidad'          => max(1, (int) round($cantARecibir)),
                'stock_anterior'    => $stockAnterior,
                'stock_nuevo'       => $stockNuevo,
                'costo_unitario'    => $item->precio_unitario,
                'costo_total'       => round(floatval($item->precio_unitario) * $cantARecibir, 2),
                'lote'              => $lote,
                'fecha_vencimiento' => $vencimiento,
                'referencia_type'   => PedidoCompra::class,
                'referencia_id'     => $this->record->id,
                'fecha_movimiento'  => now(),
                'motivo'            => "Recepción de OC {$this->record->numero}"
                    . (!empty($itemData['nota_recepcion']) ? " — {$itemData['nota_recepcion']}" : ''),
            ]);

            $stockMin = floatval($inventario->fresh()->stock_minimo);
            if ($stockMin > 0 && $stockNuevo <= $stockMin) {
                $alertasStock[] = ($item->producto->nombre ?? 'Producto')
                    . " — stock: {$stockNuevo}, mín: {$stockMin}";
            }
            $sucursalesConDeficit = InventarioAlmacen::with('almacen')
                ->where('producto_id', $item->producto_id)
                ->where('almacen_id', '!=', $almacenId)
                ->whereColumn('stock_actual', '<', 'stock_minimo')
                ->get();

            foreach ($sucursalesConDeficit as $invDestino) {
                $deficit = floatval($invDestino->stock_minimo) - floatval($invDestino->stock_actual);
                if ($deficit <= 0) continue;

                $invOrigen       = InventarioAlmacen::where('producto_id', $item->producto_id)
                    ->where('almacen_id', $almacenId)->first();
                $stockDisponible = floatval($invOrigen?->stock_actual ?? 0)
                    - floatval($invOrigen?->stock_minimo ?? 0);

                if ($stockDisponible <= 0) continue;

                $cantSugerida = min($deficit, $stockDisponible);

                $trasladosSugeridos[$invDestino->almacen_id][] = [
                    'producto_id'     => $item->producto_id,
                    'cantidad'        => $cantSugerida,
                    'producto_nombre' => $item->producto->nombre ?? 'Producto',
                    'almacen_nombre'  => $invDestino->almacen->nombre ?? 'Sucursal',
                ];
            }
        }

        $trasladosCreados = 0;
        $sinTransportista = [];

        // Transportista disponible en el almacén origen
        $transportistaOrigen = Transportista::where('almacen_id', $almacenId)
            ->where('estado', 'disponible')
            ->with('user')
            ->first();

        if ($transportistaOrigen === null && !empty($trasladosSugeridos)) {
            Notification::make()->warning()
                ->title('Sin transportista disponible')
                ->body('No hay transportista disponible en la sucursal origen. Los traslados se crearán sin asignar conductor.')
                ->persistent()
                ->send();
        }

        foreach ($trasladosSugeridos as $almacenDestinoId => $items) {
            $productosTexto = collect($items)
                ->map(fn($i) => "\"{$i['producto_nombre']}\" — déficit en {$i['almacen_nombre']}")
                ->join(', ');

            // Verificar también si hay transportista en destino para notificar
            $transportistaDestino = Transportista::where('almacen_id', $almacenDestinoId)
                ->where('estado', 'disponible')
                ->first();

            if ($transportistaDestino === null) {
                $almNombre = Almacen::find($almacenDestinoId)?->nombre ?? "almacén #{$almacenDestinoId}";
                $sinTransportista[] = $almNombre;
            }

            $traslado = Traslado::create([
                'numero'             => Traslado::generarNumero(),
                'almacen_origen_id'  => $almacenId,
                'almacen_destino_id' => $almacenDestinoId,
                'transportista_id'   => $transportistaOrigen?->id,
                'estado'             => 'sugerido',
                'motivo'             => "Redistribución automática: {$productosTexto}.",
                'creado_por'         => $userId,
            ]);

            foreach ($items as $it) {
                TrasladoItem::create([
                    'traslado_id'       => $traslado->id,
                    'producto_id'       => $it['producto_id'],
                    'cantidad_sugerida' => $it['cantidad'],
                    'lote'              => $lote,
                    'fecha_vencimiento' => $vencimiento,
                ]);
            }

            $trasladosCreados++;
        }

        $this->record->update([
            'estado'          => $todoRecibido ? 'recibido' : 'parcial',
            'fecha_recepcion' => $data['fecha_recepcion'],
        ]);

        foreach ($alertasStock as $alerta) {
            Notification::make()->warning()
                ->title('Stock bajo mínimo')
                ->body($alerta)
                ->send();
        }

        if ($trasladosCreados > 0) {
            $conductor = $transportistaOrigen?->user?->name ?? 'Sin asignar';
            $placa     = $transportistaOrigen?->vehiculo_placa ?? '—';
            Notification::make()->info()
                ->title("{$trasladosCreados} traslado(s) sugerido(s) generado(s)")
                ->body("Conductor asignado: {$conductor} ({$placa}). Revisa la sección de Traslados para aprobarlos.")
                ->persistent()
                ->send();
        }

        if (!empty($sinTransportista)) {
            Notification::make()->warning()
                ->title('Sucursales destino sin transportista')
                ->body('Sin conductor disponible en: ' . implode(', ', $sinTransportista))
                ->persistent()
                ->send();
        }

        Notification::make()->success()
            ->title($todoRecibido
                ? 'Recepción completa — inventario actualizado.'
                : 'Recepción parcial registrada. Quedan ítems pendientes.')
            ->send();

        $this->redirect(PedidoCompraResource::getUrl('view', ['record' => $this->record]));
    }
}