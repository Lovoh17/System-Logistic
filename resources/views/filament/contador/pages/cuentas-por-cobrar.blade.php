<x-filament-panels::page>
<div class="space-y-6">

    {{-- ── Encabezado ── --}}
    <div class="fi-section rounded-xl p-5 flex justify-between items-center
                bg-gradient-to-r from-amber-600 to-orange-500 text-white shadow-lg">
        <div class="flex items-center gap-3">
            <div class="bg-white/20 p-3 rounded-xl">
                <x-heroicon-o-clipboard-document-list class="w-7 h-7"/>
            </div>
            <div>
                <p class="text-sm opacity-80">Módulo</p>
                <p class="text-xl font-bold">Cuentas por Cobrar</p>
            </div>
        </div>
        <div class="text-right text-sm">
            <p class="opacity-80">Actualizado</p>
            <p class="font-semibold">{{ now()->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    {{-- ── Resumen rápido ── --}}
    @php
        $totalPendiente = \App\Models\Cliente::where('limite_credito', '>', 0)
            ->withSum(['pedidosVenta as monto_total' => fn($q) => $q->whereNotIn('estado', ['entregado','cancelado','borrador'])], 'total')
            ->get()
            ->sum('monto_total');

        $clientesConDeuda = \App\Models\Cliente::where('limite_credito', '>', 0)
            ->whereHas('pedidosVenta', fn($q) => $q->whereNotIn('estado', ['entregado','cancelado','borrador']))
            ->count();
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-filament::section class="text-center">
            <div class="text-3xl font-bold text-amber-600">${{ number_format($totalPendiente, 2) }}</div>
            <div class="text-sm text-gray-500 mt-1">Monto Total Pendiente</div>
        </x-filament::section>
        <x-filament::section class="text-center">
            <div class="text-3xl font-bold text-orange-600">{{ $clientesConDeuda }}</div>
            <div class="text-sm text-gray-500 mt-1">Clientes con Deuda Activa</div>
        </x-filament::section>
        <x-filament::section class="text-center">
            <div class="text-3xl font-bold text-teal-600">{{ \App\Models\Cliente::where('limite_credito', '>', 0)->count() }}</div>
            <div class="text-sm text-gray-500 mt-1">Total Clientes con Crédito</div>
        </x-filament::section>
    </div>

    {{-- ── Tabla ── --}}
    <x-filament::section>
        <x-slot name="heading">
            <span class="flex items-center gap-2 text-sm font-semibold">
                <x-heroicon-m-users class="w-4 h-4 text-amber-500"/>
                Detalle de Clientes
            </span>
        </x-slot>

        {{ $this->table }}
    </x-filament::section>

</div>
</x-filament-panels::page>