<x-filament-panels::page>

    {{-- Resumen del pedido --}}
    <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 px-6 py-4">
        <div class="grid grid-cols-2 gap-x-6 gap-y-3 sm:grid-cols-4 text-sm">
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">N° OC</p>
                <p class="mt-1 font-bold text-primary-600">{{ $this->record->numero }}</p>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Proveedor</p>
                <p class="mt-1 font-medium text-gray-900 dark:text-white">{{ $this->record->proveedor->nombre }}</p>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Estado</p>
                <p class="mt-1">
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                        {{ $this->record->estado === 'parcial' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800' }}">
                        {{ $this->record->estado_label }}
                    </span>
                </p>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total OC</p>
                <p class="mt-1 font-bold text-gray-900 dark:text-white">${{ number_format($this->record->total, 2) }}</p>
            </div>

            @php
                $itemsPendientes = $this->record->items->filter(fn($i) => $i->cantidad_pendiente > 0.001)->count();
                $itemsTotal      = $this->record->items->count();
            @endphp
            <div class="col-span-2 sm:col-span-4 border-t border-gray-100 dark:border-gray-800 pt-3">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Progreso de recepción</p>
                <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">
                    <span class="font-semibold text-warning-600">{{ $itemsPendientes }}</span> ítems pendientes
                    de <span class="font-semibold">{{ $itemsTotal }}</span> en total
                </p>
            </div>
        </div>
    </div>

    {{-- Formulario --}}
    <form wire:submit="submit">
        {{ $this->form }}

        <div class="flex items-center justify-end gap-3 mt-6">
            <x-filament::button
                tag="a"
                :href="\App\Filament\Resources\PedidoCompraResource::getUrl('view', ['record' => $this->record])"
                color="gray"
                icon="heroicon-o-arrow-left"
            >
                Volver
            </x-filament::button>

            <x-filament::button
                type="submit"
                color="success"
                icon="heroicon-o-inbox-arrow-down"
            >
                Registrar Recepción
            </x-filament::button>
        </div>
    </form>

</x-filament-panels::page>
