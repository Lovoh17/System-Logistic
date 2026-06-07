<x-filament-panels::page>
    <div class="space-y-4">

        <div class="flex gap-4">

            <div class="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-5 h-24">
                <div class="flex items-center justify-between h-full">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Total Productos
                        </p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-0.5">
                            {{ $this->resumen['total_productos'] }}
                        </p>
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
                            Sin Stock
                        </p>
                        <p class="text-2xl font-bold mt-0.5 {{ $this->resumen['sin_stock'] > 0 ? 'text-danger-600 dark:text-danger-400' : 'text-gray-400 dark:text-gray-500' }}">
                            {{ $this->resumen['sin_stock'] }}
                        </p>
                    </div>
                    <div class="p-2.5 rounded-xl {{ $this->resumen['sin_stock'] > 0 ? 'bg-danger-100 dark:bg-danger-900/30' : 'bg-gray-100 dark:bg-gray-700' }}">
                        <x-heroicon-m-exclamation-circle class="w-6 h-6 {{ $this->resumen['sin_stock'] > 0 ? 'text-danger-600 dark:text-danger-400' : 'text-gray-400 dark:text-gray-500' }}"/>
                    </div>
                </div>
            </div>

            <div class="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-5 h-24">
                <div class="flex items-center justify-between h-full">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Stock Bajo
                        </p>
                        <p class="text-2xl font-bold mt-0.5 {{ $this->resumen['stock_bajo'] > 0 ? 'text-warning-600 dark:text-warning-400' : 'text-gray-400 dark:text-gray-500' }}">
                            {{ $this->resumen['stock_bajo'] }}
                        </p>
                    </div>
                    <div class="p-2.5 rounded-xl {{ $this->resumen['stock_bajo'] > 0 ? 'bg-warning-100 dark:bg-warning-900/30' : 'bg-gray-100 dark:bg-gray-700' }}">
                        <x-heroicon-m-exclamation-triangle class="w-6 h-6 {{ $this->resumen['stock_bajo'] > 0 ? 'text-warning-600 dark:text-warning-400' : 'text-gray-400 dark:text-gray-500' }}"/>
                    </div>
                </div>
            </div>

            <div class="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-5 h-24">
                <div class="flex items-center justify-between h-full">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Stock Alto
                        </p>
                        <p class="text-2xl font-bold text-info-600 dark:text-info-400 mt-0.5">
                            {{ $this->resumen['stock_alto'] }}
                        </p>
                    </div>
                    <div class="p-2.5 rounded-xl bg-info-100 dark:bg-info-900/30">
                        <x-heroicon-m-arrow-trending-up class="w-6 h-6 text-info-600 dark:text-info-400"/>
                    </div>
                </div>
            </div>

            <div class="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-5 h-24">
                <div class="flex items-center justify-between h-full">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Valor Inventario
                        </p>
                        <p class="text-2xl font-bold text-success-600 dark:text-success-400 mt-0.5">
                            ${{ number_format($this->resumen['valor_inventario'], 2) }}
                        </p>
                    </div>
                    <div class="p-2.5 rounded-xl bg-success-100 dark:bg-success-900/30">
                        <x-heroicon-m-currency-dollar class="w-6 h-6 text-success-600 dark:text-success-400"/>
                    </div>
                </div>
            </div>

        </div>

        <x-filament::section>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="p-1.5 rounded-lg bg-primary-100 dark:bg-primary-900/30">
                        <x-heroicon-m-building-storefront class="w-4 h-4 text-primary-600 dark:text-primary-400"/>
                    </div>
                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                        {{ $this->sucursalActual?->nombre ?? 'Sin sucursal asignada' }}
                    </span>
                </div>
                <p class="text-xs text-gray-400 dark:text-gray-500">
                    Actualizado: {{ now()->format('d/m/Y H:i') }}
                </p>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">
                <span class="flex items-center gap-2">
                    <x-heroicon-m-table-cells class="w-4 h-4 text-gray-400"/>
                    Inventario
                </span>
            </x-slot>

            {{ $this->table }}
        </x-filament::section>

    </div>
</x-filament-panels::page>