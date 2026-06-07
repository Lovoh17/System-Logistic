<x-filament-panels::page>
    <div class="space-y-4">

        {{-- KPIs --}}
        <div class="flex gap-4">
            @foreach($this->getStats() as $stat)
                <a href="{{ $stat->getUrl() }}" class="flex-1">
                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800
                                px-4 py-5 h-24 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between h-full">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    {{ $stat->getLabel() }}
                                </p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-0.5">
                                    {{ $stat->getValue() }}
                                </p>
                            </div>
                            <div class="p-2.5 rounded-xl bg-primary-100 dark:bg-primary-900/30">
                                <x-dynamic-component
                                        :component="$stat->getIcon()"
                                        class="w-6 h-6 text-primary-600 dark:text-primary-400"
                                />
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

            {{-- Pedidos de Compra Pendientes --}}
            <x-filament::section>
                <x-slot name="heading">
                    <span class="flex items-center gap-2">
                        <x-heroicon-m-shopping-bag class="w-4 h-4 text-primary-500"/>
                        Pedidos de Compra Pendientes
                    </span>
                </x-slot>

                @forelse($this->getPedidosCompraRecientes() as $pedido)
                    <div class="flex items-center justify-between py-2.5 text-sm
                                border-b border-gray-100 dark:border-gray-800 last:border-0">
                        <div>
                            <span class="font-semibold text-primary-600 dark:text-primary-400">
                                {{ $pedido->numero }}
                            </span>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                {{ $pedido->proveedor?->nombre ?? '—' }}
                            </p>
                        </div>
                        <div class="text-right">
                            <span @class([
                                'inline-block px-2 py-0.5 text-xs rounded-full font-semibold',
                                'bg-warning-100 text-warning-700 dark:bg-warning-900/30 dark:text-warning-400'
                                    => in_array($pedido->estado, ['borrador', 'enviado']),
                                'bg-info-100 text-info-700 dark:bg-info-900/30 dark:text-info-400'
                                    => $pedido->estado === 'confirmado',
                                'bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400'
                                    => $pedido->estado === 'parcial',
                            ])>
                                {{ ucfirst($pedido->estado) }}
                            </span>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                                {{ $pedido->fecha_requerida?->format('d/m/Y') ?? '—' }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-400">
                        <x-heroicon-m-shopping-bag class="w-8 h-8 mx-auto mb-2 opacity-30"/>
                        <p class="text-sm">No hay pedidos pendientes</p>
                    </div>
                @endforelse
            </x-filament::section>

            {{-- Envios en Transito --}}
            <x-filament::section>
                <x-slot name="heading">
                    <span class="flex items-center gap-2">
                        <x-heroicon-m-truck class="w-4 h-4 text-warning-500"/>
                        Envios en Transito
                    </span>
                </x-slot>

                @forelse($this->getEnviosRecientes() as $envio)
                    <div class="flex items-center justify-between py-2.5 text-sm
                                border-b border-gray-100 dark:border-gray-800 last:border-0">
                        <div>
                            <span class="font-semibold text-primary-600 dark:text-primary-400">
                                {{ $envio->numero ?? 'Envio' }}
                            </span>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                {{ $envio->pedidoVenta?->cliente?->nombre ?? '—' }}
                            </p>
                        </div>
                        <div class="text-right">
                            <span class="inline-block px-2 py-0.5 text-xs rounded-full font-semibold
                                         bg-warning-100 text-warning-700 dark:bg-warning-900/30 dark:text-warning-400">
                                {{ ucfirst($envio->estado) }}
                            </span>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                                {{ $envio->transportista?->nombre ?? '—' }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-400">
                        <x-heroicon-m-truck class="w-8 h-8 mx-auto mb-2 opacity-30"/>
                        <p class="text-sm">No hay envios en transito</p>
                    </div>
                @endforelse
            </x-filament::section>

        </div>

    </div>
</x-filament-panels::page>