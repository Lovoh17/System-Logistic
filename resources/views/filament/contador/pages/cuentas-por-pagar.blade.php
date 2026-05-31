<x-filament-panels::page>
<div class="space-y-6">

    {{-- Encabezado --}}
    <div class="fi-section rounded-xl p-5 flex justify-between items-center
                bg-gradient-to-r from-rose-600 to-orange-500 text-white shadow-lg">
        <div class="flex items-center gap-3">
            <div class="bg-white/20 p-3 rounded-xl">
                <x-heroicon-o-truck class="w-7 h-7"/>
            </div>
            <div>
                <p class="text-sm opacity-80">Módulo</p>
                <p class="text-xl font-bold">Cuentas por Pagar</p>
            </div>
        </div>
        <div class="text-right text-sm">
            <p class="opacity-80">Actualizado</p>
            <p class="font-semibold">{{ now()->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    {{-- Resumen rápido --}}
    @php
        $totalPendiente = \App\Models\Proveedor::where('estado', 'activo')
            ->withSum(['pedidosCompra as monto_total' => fn($q) => $q->whereNotIn('estado', ['recibido','cancelado','borrador'])], 'total')
            ->get()
            ->sum('monto_total');

        $proveedoresConDeuda = \App\Models\Proveedor::where('estado', 'activo')
            ->whereHas('pedidosCompra', fn($q) => $q->whereNotIn('estado', ['recibido','cancelado','borrador']))
            ->count();
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-filament::section class="text-center">
            <div class="text-3xl font-bold text-rose-600">${{ number_format($totalPendiente, 2) }}</div>
            <div class="text-sm text-gray-500 mt-1">Monto Total Pendiente</div>
        </x-filament::section>
        <x-filament::section class="text-center">
            <div class="text-3xl font-bold text-orange-600">{{ $proveedoresConDeuda }}</div>
            <div class="text-sm text-gray-500 mt-1">Proveedores con OC Pendiente</div>
        </x-filament::section>
        <x-filament::section class="text-center">
            <div class="text-3xl font-bold text-teal-600">{{ \App\Models\Proveedor::where('estado', 'activo')->count() }}</div>
            <div class="text-sm text-gray-500 mt-1">Total Proveedores Activos</div>
        </x-filament::section>
    </div>

    {{-- Tabla --}}
    <x-filament::section>
        <x-slot name="heading">
            <span class="flex items-center gap-2 text-sm font-semibold">
                <x-heroicon-m-building-storefront class="w-4 h-4 text-rose-500"/>
                Detalle de Proveedores
            </span>
        </x-slot>

        {{ $this->table }}
    </x-filament::section>

</div>
</x-filament-panels::page>
