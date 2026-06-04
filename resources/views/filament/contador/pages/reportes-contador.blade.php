<x-filament-panels::page>
<div class="space-y-6">

    {{-- ── Encabezado ── --}}
    <div class="fi-section rounded-xl p-5 flex justify-between items-center
                bg-gradient-to-r from-emerald-700 to-teal-600 text-white shadow-lg">
        <div class="flex items-center gap-3">
            <div class="bg-white/20 p-3 rounded-xl">
                <x-heroicon-o-document-chart-bar class="w-7 h-7"/>
            </div>
            <div>
                <p class="text-sm opacity-80">Módulo</p>
                <p class="text-xl font-bold">Reportes Financieros</p>
            </div>
        </div>
        <div class="text-right">
            <p class="text-sm opacity-80">Usuario</p>
            <p class="font-semibold">{{ auth()->user()->name }}</p>
            <p class="text-xs opacity-70">{{ now()->translatedFormat('d \d\e F Y') }}</p>
        </div>
    </div>

    {{-- ── Filtros ── --}}
    <x-filament::section>
        <x-slot name="heading">
            <span class="flex items-center gap-2 text-sm font-semibold">
                <x-heroicon-m-funnel class="w-4 h-4 text-emerald-500"/>
                Filtros de Período
            </span>
        </x-slot>

        <form wire:submit.prevent="aplicarFiltros">
            {{ $this->form }}
            <div class="mt-4 flex gap-3">
                <x-filament::button type="submit" color="primary" icon="heroicon-m-magnifying-glass">
                    Aplicar Filtros
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>

    {{-- ── Tabla de Ventas ── --}}
    <x-filament::section>
        <x-slot name="heading">
            <span class="flex items-center gap-2 text-sm font-semibold">
                <x-heroicon-m-shopping-cart class="w-4 h-4 text-emerald-500"/>
                Ventas del Período
            </span>
        </x-slot>

        {{ $this->table }}
    </x-filament::section>

</div>
</x-filament-panels::page>