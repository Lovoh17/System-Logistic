<x-filament-panels::page>
<div class="space-y-6">

    {{-- ── Encabezado ── --}}
    <div class="fi-section rounded-xl p-5 flex justify-between items-center
                bg-gradient-to-r from-emerald-700 to-teal-600 text-white shadow-lg">
        <div class="flex items-center gap-3">
            <div class="bg-white/20 p-3 rounded-xl">
                <x-heroicon-o-scale class="w-7 h-7"/>
            </div>
            <div>
                <p class="text-sm opacity-80">Análisis Financiero</p>
                <p class="text-xl font-bold">Estado de Resultados</p>
            </div>
        </div>
        <div class="text-right text-sm opacity-80">
            <p>Período analizado</p>
            <p class="font-semibold text-base text-white">
                {{ \Carbon\Carbon::parse($this->data['fecha_inicio'] ?? now())->format('d/m/Y') }}
                — {{ \Carbon\Carbon::parse($this->data['fecha_fin'] ?? now())->format('d/m/Y') }}
            </p>
        </div>
    </div>

    {{-- ── Filtro de período ── --}}
    <x-filament::section>
        <x-slot name="heading">
            <span class="flex items-center gap-2 text-sm font-semibold">
                <x-heroicon-m-calendar-days class="w-4 h-4 text-emerald-500"/>
                Período de Análisis
            </span>
        </x-slot>

        <form wire:submit.prevent="calcular">
            {{ $this->form }}
            <div class="mt-4">
                <x-filament::button type="submit" color="primary" icon="heroicon-m-arrow-path">
                    Recalcular
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>

    {{-- ── Estado de Resultados ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Columna principal: Estado de Resultados ── --}}
        <div class="lg:col-span-2 space-y-4">
            <x-filament::section>
                <x-slot name="heading">
                    <span class="font-bold text-base text-emerald-700 dark:text-emerald-400">
                        ESTADO DE RESULTADOS
                    </span>
                </x-slot>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">

                            {{-- Ingresos --}}
                            <tr class="bg-emerald-50 dark:bg-emerald-900/20">
                                <td colspan="2" class="py-2 px-4 font-bold text-emerald-800 dark:text-emerald-300 text-xs uppercase tracking-wider">
                                    INGRESOS
                                </td>
                            </tr>
                            <tr>
                                <td class="py-3 px-4 text-gray-700 dark:text-gray-300">
                                    Ventas brutas ({{ $this->totalPedidosVenta }} pedidos)
                                </td>
                                <td class="py-3 px-4 text-right font-semibold text-emerald-600">
                                    ${{ number_format($this->ingresos, 2) }}
                                </td>
                            </tr>

                            {{-- Costo de Ventas --}}
                            <tr class="bg-amber-50 dark:bg-amber-900/20">
                                <td colspan="2" class="py-2 px-4 font-bold text-amber-800 dark:text-amber-300 text-xs uppercase tracking-wider">
                                    COSTO DE VENTAS
                                </td>
                            </tr>
                            <tr>
                                <td class="py-3 px-4 text-gray-700 dark:text-gray-300">
                                    Costo de productos vendidos
                                </td>
                                <td class="py-3 px-4 text-right font-semibold text-amber-600">
                                    (${{ number_format($this->costoVentas, 2) }})
                                </td>
                            </tr>

                            {{-- Utilidad Bruta --}}
                            <tr class="border-t-2 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800">
                                <td class="py-3 px-4 font-bold text-gray-800 dark:text-gray-200">
                                    UTILIDAD BRUTA
                                </td>
                                <td class="py-3 px-4 text-right font-bold text-lg
                                    {{ $this->utilidadBruta >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                    ${{ number_format($this->utilidadBruta, 2) }}
                                </td>
                            </tr>

                            {{-- Gastos Operativos --}}
                            <tr class="bg-red-50 dark:bg-red-900/20">
                                <td colspan="2" class="py-2 px-4 font-bold text-red-800 dark:text-red-300 text-xs uppercase tracking-wider">
                                    GASTOS OPERATIVOS
                                </td>
                            </tr>
                            <tr>
                                <td class="py-3 px-4 text-gray-700 dark:text-gray-300">
                                    Compras del período ({{ $this->totalPedidosCompra }} órdenes)
                                </td>
                                <td class="py-3 px-4 text-right font-semibold text-rose-600">
                                    (${{ number_format($this->gastosOperativos, 2) }})
                                </td>
                            </tr>

                            {{-- Utilidad Neta --}}
                            <tr class="border-t-2 border-gray-400 dark:border-gray-500
                                {{ $this->utilidadNeta >= 0 ? 'bg-emerald-100 dark:bg-emerald-900/40' : 'bg-rose-100 dark:bg-rose-900/40' }}">
                                <td class="py-4 px-4 font-bold text-lg text-gray-900 dark:text-white">
                                    UTILIDAD NETA
                                </td>
                                <td class="py-4 px-4 text-right font-bold text-xl
                                    {{ $this->utilidadNeta >= 0 ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-700 dark:text-rose-400' }}">
                                    ${{ number_format($this->utilidadNeta, 2) }}
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>
            </x-filament::section>
        </div>

        {{-- Columna lateral: Métricas ── --}}
        <div class="space-y-4">

            <x-filament::section>
                <x-slot name="heading">Indicadores Clave</x-slot>

                <div class="space-y-4">

                    <div class="text-center p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20">
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Margen Bruto</p>
                        <p class="text-3xl font-bold {{ $this->margenBruto >= 0 ? 'text-emerald-600' : 'text-rose-500' }}">
                            {{ $this->margenBruto }}%
                        </p>
                        <p class="text-xs text-gray-400 mt-1">Utilidad bruta / Ventas</p>
                    </div>

                    <div class="text-center p-4 rounded-xl bg-teal-50 dark:bg-teal-900/20">
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">Margen Neto</p>
                        <p class="text-3xl font-bold {{ $this->margenNeto >= 0 ? 'text-teal-600' : 'text-rose-500' }}">
                            {{ $this->margenNeto }}%
                        </p>
                        <p class="text-xs text-gray-400 mt-1">Utilidad neta / Ventas</p>
                    </div>

                    <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800 space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Ventas totales</span>
                            <span class="font-semibold text-emerald-600">${{ number_format($this->ingresos, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Costo mercancía</span>
                            <span class="font-semibold text-amber-600">${{ number_format($this->costoVentas, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Gastos compras</span>
                            <span class="font-semibold text-rose-600">${{ number_format($this->gastosOperativos, 2) }}</span>
                        </div>
                        <div class="border-t dark:border-gray-600 pt-2 flex justify-between font-bold">
                            <span class="dark:text-white">Resultado neto</span>
                            <span class="{{ $this->utilidadNeta >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                ${{ number_format($this->utilidadNeta, 2) }}
                            </span>
                        </div>
                    </div>
                </div>
            </x-filament::section>

        </div>
    </div>

</div>
</x-filament-panels::page>