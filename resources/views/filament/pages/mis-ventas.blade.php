<x-filament-panels::page>
    <div class="space-y-4">

        <x-filament::section>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-xl bg-primary-100 dark:bg-primary-900/30">
                        <x-heroicon-m-building-storefront class="w-5 h-5 text-primary-600 dark:text-primary-400"/>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Sucursal
                        </p>
                        <p class="text-base font-bold text-gray-900 dark:text-gray-100">
                            {{ $this->sucursalActual?->nombre ?? 'No asignada' }}
                        </p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        Atendido por
                    </p>
                    <p class="text-sm font-bold text-gray-800 dark:text-gray-200">
                        {{ auth()->user()->name }}
                    </p>
                </div>
            </div>
        </x-filament::section>

        <div class="flex gap-4">

            <div class="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-5 h-24">
                <div class="flex items-center justify-between h-full">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Total Ventas
                        </p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-0.5">
                            {{ $this->resumen['total_ventas'] }}
                        </p>
                    </div>
                    <div class="p-2.5 rounded-xl bg-primary-100 dark:bg-primary-900/30">
                        <x-heroicon-m-shopping-cart class="w-6 h-6 text-primary-600 dark:text-primary-400"/>
                    </div>
                </div>
            </div>

            <div class="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-5 h-24">
                <div class="flex items-center justify-between h-full">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Monto Total
                        </p>
                        <p class="text-2xl font-bold text-success-600 dark:text-success-400 mt-0.5">
                            ${{ number_format($this->resumen['total_monto'], 2) }}
                        </p>
                    </div>
                    <div class="p-2.5 rounded-xl bg-success-100 dark:bg-success-900/30">
                        <x-heroicon-m-currency-dollar class="w-6 h-6 text-success-600 dark:text-success-400"/>
                    </div>
                </div>
            </div>

            <div class="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-5 h-24">
                <div class="flex items-center justify-between h-full">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Ventas Hoy
                        </p>
                        <p class="text-2xl font-bold text-info-600 dark:text-info-400 mt-0.5">
                            {{ $this->resumen['ventas_hoy'] }}
                        </p>
                    </div>
                    <div class="p-2.5 rounded-xl bg-info-100 dark:bg-info-900/30">
                        <x-heroicon-m-calendar-days class="w-6 h-6 text-info-600 dark:text-info-400"/>
                    </div>
                </div>
            </div>

            <div class="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-5 h-24">
                <div class="flex items-center justify-between h-full">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Monto Hoy
                        </p>
                        <p class="text-2xl font-bold text-warning-600 dark:text-warning-400 mt-0.5">
                            ${{ number_format($this->resumen['monto_hoy'], 2) }}
                        </p>
                    </div>
                    <div class="p-2.5 rounded-xl bg-warning-100 dark:bg-warning-900/30">
                        <x-heroicon-m-banknotes class="w-6 h-6 text-warning-600 dark:text-warning-400"/>
                    </div>
                </div>
            </div>

        </div>

        <x-filament::section>
            <x-slot name="heading">
            <span class="flex items-center gap-2">
                <x-heroicon-m-shopping-cart class="w-4 h-4 text-primary-500"/>
                Historial de Ventas
            </span>
            </x-slot>

            {{ $this->table }}
        </x-filament::section>

    </div>
</x-filament-panels::page>