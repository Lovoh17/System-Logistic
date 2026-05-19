@php use Illuminate\Support\Str; @endphp

<x-filament-panels::page>
<div class="space-y-4">

    {{-- ── Banner sucursal ── --}}
    <div class="fi-section rounded-xl p-4 flex justify-between items-center
                bg-gradient-to-r from-primary-700 to-primary-600 text-white shadow">
        <div class="flex items-center gap-3">
            <div class="bg-white/20 p-2 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div>
                <p class="text-xs opacity-75">Sucursal</p>
                <p class="font-bold">{{ $this->sucursalActual?->nombre ?? 'No asignada' }}</p>
            </div>
        </div>
        <div class="text-right">
            <p class="text-xs opacity-75">Atendido por</p>
            <p class="font-bold">{{ auth()->user()->name }}</p>
        </div>
    </div>

    {{-- ── Tarjetas de resumen ── --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <x-filament::section class="text-center">
            <div class="text-2xl font-bold text-primary-600 dark:text-primary-400">
                {{ $this->resumen['total_ventas'] }}
            </div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Total Ventas</div>
        </x-filament::section>

        <x-filament::section class="text-center">
            <div class="text-2xl font-bold text-primary-600 dark:text-primary-400">
                ${{ number_format($this->resumen['total_monto'], 2) }}
            </div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Monto Total</div>
        </x-filament::section>

        <x-filament::section class="text-center">
            <div class="text-2xl font-bold text-primary-600 dark:text-primary-400">
                {{ $this->resumen['ventas_hoy'] }}
            </div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Ventas Hoy</div>
        </x-filament::section>

        <x-filament::section class="text-center">
            <div class="text-2xl font-bold text-primary-600 dark:text-primary-400">
                ${{ number_format($this->resumen['monto_hoy'], 2) }}
            </div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Monto Hoy</div>
        </x-filament::section>
    </div>

    {{-- ── Tabla de ventas ── --}}
    <x-filament::section>
        <x-slot name="heading">
            <span class="flex items-center gap-2 text-sm font-semibold">
                <x-heroicon-m-shopping-cart class="w-4 h-4 text-primary-500"/>
                Historial de Ventas
            </span>
        </x-slot>

        {{ $this->table }}
    </x-filament::section>
</div>
</x-filament-panels::page>