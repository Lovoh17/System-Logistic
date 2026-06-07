<x-filament-panels::page>

    @php
        $inventario   = $this->getResumenInventario();
        $topProductos = $this->getTopProductos();
    @endphp

    <div class="space-y-4">

        <div class="flex gap-4">

            <div class="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-5 h-24">
                <div class="flex items-center justify-between h-full">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Total Productos
                        </p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-0.5">
                            {{ $inventario['total'] }}
                        </p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">En tu sucursal</p>
                    </div>
                    <div class="p-2.5 rounded-xl bg-primary-100 dark:bg-primary-900/30">
                        <x-heroicon-m-cube class="w-6 h-6 text-primary-600 dark:text-primary-400"/>
                    </div>
                </div>
            </div>

            <div class="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-5 h-24">
                <div class="flex items-center justify-between h-full">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Stock Critico
                        </p>
                        <p @class([
                            'text-2xl font-bold mt-0.5',
                            'text-danger-600 dark:text-danger-400'   => $inventario['critico'] > 0,
                            'text-success-600 dark:text-success-400' => $inventario['critico'] === 0,
                        ])>{{ $inventario['critico'] }}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Por debajo del minimo</p>
                    </div>
                    <div @class([
                        'p-2.5 rounded-xl',
                        'bg-danger-100 dark:bg-danger-900/30'   => $inventario['critico'] > 0,
                        'bg-success-100 dark:bg-success-900/30' => $inventario['critico'] === 0,
                    ])>
                        <x-heroicon-m-exclamation-triangle @class([
                            'w-6 h-6',
                            'text-danger-600 dark:text-danger-400'   => $inventario['critico'] > 0,
                            'text-success-600 dark:text-success-400' => $inventario['critico'] === 0,
                        ])/>
                    </div>
                </div>
            </div>

            <div class="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-5 h-24">
                <div class="flex items-center justify-between h-full">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Excedentes
                        </p>
                        <p @class([
                            'text-2xl font-bold mt-0.5',
                            'text-warning-600 dark:text-warning-400' => $inventario['excedente'] > 0,
                            'text-gray-400 dark:text-gray-500'       => $inventario['excedente'] === 0,
                        ])>{{ $inventario['excedente'] }}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Por encima del maximo</p>
                    </div>
                    <div @class([
                        'p-2.5 rounded-xl',
                        'bg-warning-100 dark:bg-warning-900/30' => $inventario['excedente'] > 0,
                        'bg-gray-100 dark:bg-gray-700'          => $inventario['excedente'] === 0,
                    ])>
                        <x-heroicon-m-arrow-trending-up @class([
                            'w-6 h-6',
                            'text-warning-600 dark:text-warning-400' => $inventario['excedente'] > 0,
                            'text-gray-400 dark:text-gray-500'       => $inventario['excedente'] === 0,
                        ])/>
                    </div>
                </div>
            </div>

        </div>

        @if(!empty($topProductos))
            <x-filament::section>
                <x-slot name="heading">
                    <span class="flex items-center gap-2">
                        <x-heroicon-m-arrow-trending-up class="w-4 h-4 text-primary-500"/>
                        Top Productos — Salidas
                    </span>
                </x-slot>

                <div class="space-y-3">
                    @foreach($topProductos as $i => $item)
                        @php $max = $topProductos[0]['cantidad'] ?: 1; @endphp
                        <div class="flex items-center gap-3">
                            <span class="w-5 text-xs font-bold text-gray-400 dark:text-gray-500 flex-shrink-0">
                                {{ $i + 1 }}
                            </span>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-1">
                                    <p class="text-sm font-medium text-gray-700 dark:text-gray-200 truncate">
                                        {{ $item['producto'] }}
                                    </p>
                                    <span class="text-sm font-semibold text-primary-600 dark:text-primary-400 ml-3 flex-shrink-0">
                                        {{ number_format($item['cantidad'], 2) }} u.
                                    </span>
                                </div>
                                <div class="h-1.5 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                                    <div class="h-1.5 bg-primary-500 rounded-full transition-all"
                                         style="width: {{ round(($item['cantidad'] / $max) * 100) }}%">
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-filament::section>
        @endif

        <x-filament::section>
            <x-slot name="heading">
                <span class="flex items-center gap-2">
                    <x-heroicon-m-funnel class="w-4 h-4 text-gray-400"/>
                    Filtros y Exportacion
                </span>
            </x-slot>

            <form wire:submit="aplicarFiltros">
                {{ $this->form }}
                <div class="flex flex-wrap items-center gap-2 mt-4">
                    <x-filament::button
                            type="submit"
                            color="primary"
                            icon="heroicon-m-funnel"
                            size="sm"
                    >
                        Aplicar Filtros
                    </x-filament::button>

                    <x-filament::button
                            wire:click="exportarExcel"
                            wire:loading.attr="disabled"
                            color="success"
                            icon="heroicon-m-table-cells"
                            size="sm"
                    >
                        <span wire:loading.remove wire:target="exportarExcel">Exportar Excel</span>
                        <span wire:loading wire:target="exportarExcel">Generando...</span>
                    </x-filament::button>

                    <x-filament::button
                            wire:click="exportarPdf"
                            wire:loading.attr="disabled"
                            color="danger"
                            icon="heroicon-m-document-arrow-down"
                            size="sm"
                    >
                        <span wire:loading.remove wire:target="exportarPdf">Exportar PDF</span>
                        <span wire:loading wire:target="exportarPdf">Generando...</span>
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">
                <span class="flex items-center gap-2">
                    <x-heroicon-m-table-cells class="w-4 h-4 text-gray-400"/>
                    Resultados
                    @php $tipo = $this->data['tipo_reporte'] ?? 'ventas'; @endphp
                    <span class="bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400
                                 text-xs font-bold px-2 py-0.5 rounded-full">
                        {{ match($tipo) {
                            'ventas'   => 'Ventas',
                            'salidas'  => 'Salidas',
                            'ingresos' => 'Ingresos',
                            default    => 'Reporte',
                        } }}
                    </span>
                </span>
            </x-slot>

            {{ $this->table }}
        </x-filament::section>

    </div>

</x-filament-panels::page>