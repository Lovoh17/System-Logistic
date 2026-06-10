<x-filament-panels::page>
    @php
        $producto          = $this->record;
        $stocks            = $this->getStockData();
        $stockTotal        = $stocks->sum('stock_actual');
        $totalSucursales   = $stocks->count();
        $sucursalesCritico = $stocks->filter(fn($s) => $s->stock_actual <= $s->stock_minimo)->count();
    @endphp

    <div class="space-y-3">

        {{-- KPIs — flex en vez de grid para garantizar fila horizontal --}}
        <div class="flex gap-3">

            <div class="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-4 min-w-0">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-[11px] font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Stock Total</p>
                        <p class="text-xl font-medium text-gray-900 dark:text-gray-100 truncate">
                            {{ number_format($stockTotal, 2) }}
                            <span class="text-xs font-normal text-gray-400 ml-1">{{ $producto->unidad_medida }}</span>
                        </p>
                    </div>
                    <div class="w-9 h-9 rounded-lg bg-primary-50 dark:bg-primary-900/30 flex items-center justify-center flex-shrink-0">
                        <x-heroicon-m-archive-box class="w-5 h-5 text-primary-600 dark:text-primary-400"/>
                    </div>
                </div>
            </div>

            <div class="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-4 min-w-0">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-[11px] font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Sucursales</p>
                        <p class="text-xl font-medium text-gray-900 dark:text-gray-100">{{ $totalSucursales }}</p>
                        <p class="text-[11px] text-gray-400 mt-0.5">con inventario</p>
                    </div>
                    <div class="w-9 h-9 rounded-lg bg-info-50 dark:bg-info-900/30 flex items-center justify-center flex-shrink-0">
                        <x-heroicon-m-building-storefront class="w-5 h-5 text-info-600 dark:text-info-400"/>
                    </div>
                </div>
            </div>

            <div class="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-4 min-w-0">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-[11px] font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Alertas</p>
                        @if($sucursalesCritico > 0)
                            <p class="text-xl font-medium text-danger-600 dark:text-danger-400">{{ $sucursalesCritico }}</p>
                        @else
                            <p class="text-xl font-medium text-success-600 dark:text-success-400">{{ $sucursalesCritico }}</p>
                        @endif
                        <p class="text-[11px] text-gray-400 mt-0.5">bajo mínimo</p>
                    </div>
                    @if($sucursalesCritico > 0)
                        <div class="w-9 h-9 rounded-lg bg-danger-50 dark:bg-danger-900/30 flex items-center justify-center flex-shrink-0">
                            <x-heroicon-m-exclamation-triangle class="w-5 h-5 text-danger-600 dark:text-danger-400"/>
                        </div>
                    @else
                        <div class="w-9 h-9 rounded-lg bg-success-50 dark:bg-success-900/30 flex items-center justify-center flex-shrink-0">
                            <x-heroicon-m-check-circle class="w-5 h-5 text-success-600 dark:text-success-400"/>
                        </div>
                    @endif
                </div>
            </div>

            <div class="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-4 min-w-0">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-[11px] font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Precio Venta</p>
                        <p class="text-xl font-medium text-success-600 dark:text-success-400">
                            ${{ number_format($producto->precio_venta, 2) }}
                        </p>
                    </div>
                    <div class="w-9 h-9 rounded-lg bg-success-50 dark:bg-success-900/30 flex items-center justify-center flex-shrink-0">
                        <x-heroicon-m-currency-dollar class="w-5 h-5 text-success-600 dark:text-success-400"/>
                    </div>
                </div>
            </div>

        </div>

        {{-- Tabla stock por sucursal --}}
        <x-filament::section>
            <x-slot name="heading">
                <span class="flex items-center gap-2 text-sm">
                    <x-heroicon-m-chart-bar class="w-4 h-4 text-primary-500"/>
                    Stock por Sucursal
                    <span class="bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400 text-xs font-medium px-2 py-0.5 rounded-full">
                        {{ $totalSucursales }}
                    </span>
                </span>
            </x-slot>

            <div class="overflow-x-auto -mx-6 -mb-6">
                <table class="w-full text-sm">
                    <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/60 border-b border-gray-200 dark:border-gray-700">
                        <th class="text-left py-2.5 px-6 text-[11px] font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Sucursal</th>
                        <th class="text-right py-2.5 px-4 text-[11px] font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Stock Actual</th>
                        <th class="text-right py-2.5 px-4 text-[11px] font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Mínimo</th>
                        <th class="text-right py-2.5 px-4 text-[11px] font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Máximo</th>
                        <th class="text-right py-2.5 px-4 text-[11px] font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Reorden</th>
                        <th class="text-center py-2.5 px-4 text-[11px] font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500">Estado</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($stocks as $inv)
                        @php
                            $critico    = $inv->stock_actual <= $inv->stock_minimo;
                            $porcentaje = $inv->stock_maximo > 0
                                ? min(100, round(($inv->stock_actual / $inv->stock_maximo) * 100))
                                : 0;
                        @endphp
                        <tr class="hover:bg-gray-50/60 dark:hover:bg-white/[0.03] transition-colors">
                            <td class="py-3.5 px-6">
                                <p class="font-medium text-gray-800 dark:text-gray-200">{{ $inv->almacen?->nombre ?? '—' }}</p>
                                @if($inv->almacen?->responsable)
                                    <p class="text-[11px] text-gray-400 mt-0.5">{{ $inv->almacen->responsable }}</p>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                @if($critico)
                                    <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-medium tabular-nums bg-danger-50 text-danger-700 dark:bg-danger-900/30 dark:text-danger-400">
                                            {{ number_format($inv->stock_actual, 2) }}
                                        </span>
                                @else
                                    <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-medium tabular-nums bg-success-50 text-success-700 dark:bg-success-900/30 dark:text-success-400">
                                            {{ number_format($inv->stock_actual, 2) }}
                                        </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right tabular-nums text-gray-500 dark:text-gray-400 text-xs">
                                {{ number_format($inv->stock_minimo, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-right tabular-nums text-gray-500 dark:text-gray-400 text-xs">
                                {{ number_format($inv->stock_maximo, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-right tabular-nums text-gray-500 dark:text-gray-400 text-xs">
                                {{ number_format($inv->punto_reorden, 2) }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex flex-col items-center gap-1.5">
                                    @if($critico)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-danger-50 text-danger-700 dark:bg-danger-900/30 dark:text-danger-400">
                                                <x-heroicon-m-exclamation-triangle class="w-3 h-3"/>
                                                Crítico
                                            </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-success-50 text-success-700 dark:bg-success-900/30 dark:text-success-400">
                                                <x-heroicon-m-check-circle class="w-3 h-3"/>
                                                Disponible
                                            </span>
                                    @endif
                                    {{-- Barra de progreso con style inline para evitar purge de Tailwind --}}
                                    @if($inv->stock_maximo > 0)
                                        <div class="w-16 rounded-full h-1 overflow-hidden" style="background:rgba(156,163,175,0.3)">
                                            <div
                                                    class="h-1 rounded-full"
                                                    style="width:{{ $porcentaje }}%; background:{{ $critico ? '#ef4444' : '#22c55e' }}"
                                            ></div>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-14 text-center text-gray-400">
                                <x-heroicon-m-archive-box class="w-9 h-9 mx-auto mb-2 opacity-25"/>
                                <p class="text-sm">No hay configuración de stock para este producto.</p>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                    @if($stocks->count() > 1)
                        <tfoot>
                        <tr class="bg-gray-50 dark:bg-gray-800/60 border-t-2 border-gray-200 dark:border-gray-700">
                            <td class="py-3 px-6 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total</td>
                            <td class="py-3 px-4 text-right font-medium tabular-nums text-gray-800 dark:text-gray-200">
                                {{ number_format($stockTotal, 2) }}
                                <span class="font-normal text-gray-400 text-xs ml-1">{{ $producto->unidad_medida }}</span>
                            </td>
                            <td colspan="4"></td>
                        </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </x-filament::section>

        {{-- Datos del producto --}}
        <x-filament::section>
            <x-slot name="heading">
                <span class="flex items-center gap-2 text-sm">
                    <x-heroicon-m-identification class="w-4 h-4 text-primary-500"/>
                    Datos del Producto
                </span>
            </x-slot>

            <div class="-mx-6 -mb-6 grid grid-cols-2 lg:grid-cols-4 divide-x divide-y divide-gray-100 dark:divide-gray-800">
                <div class="px-6 py-4">
                    <p class="text-[11px] font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Código</p>
                    <p class="font-mono text-sm font-medium text-gray-800 dark:text-gray-200">{{ $producto->codigo }}</p>
                </div>
                <div class="px-6 py-4">
                    <p class="text-[11px] font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">SKU</p>
                    <p class="font-mono text-sm text-gray-700 dark:text-gray-300">{{ $producto->sku ?? '—' }}</p>
                </div>
                <div class="px-6 py-4">
                    <p class="text-[11px] font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Categoría</p>
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $producto->categoria?->nombre ?? '—' }}</p>
                </div>
                <div class="px-6 py-4">
                    <p class="text-[11px] font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Unidad</p>
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $producto->unidad_medida }}</p>
                </div>
                <div class="px-6 py-4">
                    <p class="text-[11px] font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Precio Compra</p>
                    <p class="text-sm text-gray-700 dark:text-gray-300">${{ number_format($producto->precio_compra, 2) }}</p>
                </div>
                <div class="px-6 py-4">
                    <p class="text-[11px] font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Precio Venta</p>
                    <p class="text-sm font-medium text-success-600 dark:text-success-400">${{ number_format($producto->precio_venta, 2) }}</p>
                </div>
                <div class="px-6 py-4">
                    <p class="text-[11px] font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Refrigeración</p>
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $producto->requiere_refrigeracion ? 'Requerida' : 'No requerida' }}</p>
                </div>
                <div class="px-6 py-4">
                    <p class="text-[11px] font-medium uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Estado</p>
                    @if($producto->estado === 'activo')
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-success-50 text-success-700 dark:bg-success-900/30 dark:text-success-400">
                            {{ ucfirst($producto->estado) }}
                        </span>
                    @else
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                            {{ ucfirst($producto->estado) }}
                        </span>
                    @endif
                </div>
            </div>
        </x-filament::section>

    </div>
</x-filament-panels::page>