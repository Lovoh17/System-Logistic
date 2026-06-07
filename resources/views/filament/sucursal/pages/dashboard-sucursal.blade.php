<x-filament-panels::page>

    @php
        $ventas              = $this->getVentasMes();
        $pedidosPendientes   = $this->getPedidosPendientes();
        $stockCritico        = $this->getStockCritico();
        $trasladosPendientes = $this->getTrasladosPendientes();
        $almacen             = auth()->user()?->almacen;
    @endphp

    <div class="space-y-4">

        @if($almacen)
            <x-filament::section>
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-xl bg-primary-100 dark:bg-primary-900/30">
                        <x-heroicon-m-building-storefront class="w-6 h-6 text-primary-600 dark:text-primary-400"/>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Sucursal activa
                        </p>
                        <p class="text-base font-bold text-gray-900 dark:text-gray-100">
                            {{ $almacen->nombre }}
                        </p>
                    </div>
                </div>
            </x-filament::section>
        @endif

        <div class="flex gap-4">

            <div class="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-5 h-24">
                <div class="flex items-center justify-between h-full">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Ventas del Mes
                        </p>
                        <p class="text-2xl font-bold text-success-600 dark:text-success-400 mt-0.5">
                            ${{ number_format($ventas['total'], 2) }}
                        </p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                            {{ $ventas['count'] }} pedidos
                        </p>
                    </div>
                    <div class="p-2.5 rounded-xl bg-success-100 dark:bg-success-900/30">
                        <x-heroicon-m-shopping-cart class="w-6 h-6 text-success-600 dark:text-success-400"/>
                    </div>
                </div>
            </div>

            <div class="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-5 h-24">
                <div class="flex items-center justify-between h-full">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Pedidos Activos
                        </p>
                        <p @class([
                            'text-2xl font-bold mt-0.5',
                            'text-warning-600 dark:text-warning-400' => $pedidosPendientes > 0,
                            'text-gray-400 dark:text-gray-500'       => $pedidosPendientes === 0,
                        ])>
                            {{ $pedidosPendientes }}
                        </p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Pendientes de despacho</p>
                    </div>
                    <div @class([
                        'p-2.5 rounded-xl',
                        'bg-warning-100 dark:bg-warning-900/30' => $pedidosPendientes > 0,
                        'bg-gray-100 dark:bg-gray-700'          => $pedidosPendientes === 0,
                    ])>
                        <x-heroicon-m-document-text @class([
                            'w-6 h-6',
                            'text-warning-600 dark:text-warning-400' => $pedidosPendientes > 0,
                            'text-gray-400 dark:text-gray-500'       => $pedidosPendientes === 0,
                        ])/>
                    </div>
                </div>
            </div>

            <div class="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-5 h-24">
                <div class="flex items-center justify-between h-full">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Stock Crítico
                        </p>
                        <p @class([
                            'text-2xl font-bold mt-0.5',
                            'text-danger-600 dark:text-danger-400' => $stockCritico > 0,
                            'text-success-600 dark:text-success-400' => $stockCritico === 0,
                        ])>
                            {{ $stockCritico }}
                        </p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Productos bajo mínimo</p>
                    </div>
                    <div @class([
                        'p-2.5 rounded-xl',
                        'bg-danger-100 dark:bg-danger-900/30'   => $stockCritico > 0,
                        'bg-success-100 dark:bg-success-900/30' => $stockCritico === 0,
                    ])>
                        <x-heroicon-m-exclamation-triangle @class([
                            'w-6 h-6',
                            'text-danger-600 dark:text-danger-400'   => $stockCritico > 0,
                            'text-success-600 dark:text-success-400' => $stockCritico === 0,
                        ])/>
                    </div>
                </div>
            </div>

            <div class="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-5 h-24">
                <div class="flex items-center justify-between h-full">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Traslados
                        </p>
                        <p @class([
                            'text-2xl font-bold mt-0.5',
                            'text-info-600 dark:text-info-400' => $trasladosPendientes > 0,
                            'text-gray-400 dark:text-gray-500' => $trasladosPendientes === 0,
                        ])>
                            {{ $trasladosPendientes }}
                        </p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Sugeridos o por completar</p>
                    </div>
                    <div @class([
                        'p-2.5 rounded-xl',
                        'bg-info-100 dark:bg-info-900/30' => $trasladosPendientes > 0,
                        'bg-gray-100 dark:bg-gray-700'    => $trasladosPendientes === 0,
                    ])>
                        <x-heroicon-m-arrow-path @class([
                            'w-6 h-6',
                            'text-info-600 dark:text-info-400' => $trasladosPendientes > 0,
                            'text-gray-400 dark:text-gray-500' => $trasladosPendientes === 0,
                        ])/>
                    </div>
                </div>
            </div>

        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

            <x-filament::section>
                <x-slot name="heading">
                    <span class="flex items-center gap-2">
                        <x-heroicon-m-shopping-cart class="w-4 h-4 text-success-500"/>
                        Últimas Ventas
                    </span>
                </x-slot>
                <x-slot name="headerEnd">
                    <a href="/sucursal/pedido-venta-sucursals"
                       class="text-xs text-primary-600 dark:text-primary-400 hover:underline font-medium">
                        Ver todas →
                    </a>
                </x-slot>

                @forelse($this->getUltimasVentas() as $venta)
                    <div class="flex items-center justify-between py-2.5 text-sm
                                border-b border-gray-100 dark:border-gray-800 last:border-0">
                        <div class="min-w-0">
                            <span class="font-semibold text-primary-600 dark:text-primary-400">
                                {{ $venta->numero }}
                            </span>
                            <span class="text-gray-500 dark:text-gray-400 ml-2 text-xs truncate">
                                {{ $venta->cliente?->nombre }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <span class="font-semibold text-gray-800 dark:text-gray-200">
                                ${{ number_format($venta->total, 2) }}
                            </span>
                            <span @class([
                                'text-xs px-2 py-0.5 rounded-full font-semibold',
                                'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400'
                                    => $venta->estado === 'entregado',
                                'bg-warning-100 text-warning-700 dark:bg-warning-900/30 dark:text-warning-400'
                                    => in_array($venta->estado, ['en_preparacion', 'listo']),
                                'bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-400'
                                    => $venta->estado === 'cancelado',
                                'bg-info-100 text-info-700 dark:bg-info-900/30 dark:text-info-400'
                                    => !in_array($venta->estado, ['entregado','en_preparacion','listo','cancelado']),
                            ])>
                                {{ ucfirst(str_replace('_', ' ', $venta->estado)) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-400">
                        <x-heroicon-m-shopping-cart class="w-8 h-8 mx-auto mb-2 opacity-30"/>
                        <p class="text-sm">Sin ventas este mes.</p>
                    </div>
                @endforelse
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">
                    <span class="flex items-center gap-2">
                        <x-heroicon-m-arrow-path class="w-4 h-4 text-info-500"/>
                        Traslados Recientes
                    </span>
                </x-slot>
                <x-slot name="headerEnd">
                    <a href="/sucursal/traslado-sucursals"
                       class="text-xs text-primary-600 dark:text-primary-400 hover:underline font-medium">
                        Ver todos →
                    </a>
                </x-slot>

                @forelse($this->getUltimosTrasladosSucursal() as $traslado)
                    <div class="flex items-center justify-between py-2.5 text-sm
                                border-b border-gray-100 dark:border-gray-800 last:border-0">
                        <div class="min-w-0">
                            <span class="font-semibold text-primary-600 dark:text-primary-400">
                                {{ $traslado->numero }}
                            </span>
                            <span class="text-gray-400 dark:text-gray-500 ml-1 text-xs">
                                {{ $traslado->almacenOrigen?->nombre }} → {{ $traslado->almacenDestino?->nombre }}
                            </span>
                        </div>
                        <span @class([
                            'text-xs font-semibold px-2 py-0.5 rounded-full flex-shrink-0',
                            'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400'
                                => $traslado->estado === 'completado',
                            'bg-info-100 text-info-700 dark:bg-info-900/30 dark:text-info-400'
                                => $traslado->estado === 'aprobado',
                            'bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-400'
                                => $traslado->estado === 'cancelado',
                            'bg-warning-100 text-warning-700 dark:bg-warning-900/30 dark:text-warning-400'
                                => $traslado->estado === 'sugerido',
                        ])>
                            {{ ucfirst($traslado->estado) }}
                        </span>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-400">
                        <x-heroicon-m-arrow-path class="w-8 h-8 mx-auto mb-2 opacity-30"/>
                        <p class="text-sm">Sin traslados recientes.</p>
                    </div>
                @endforelse
            </x-filament::section>

        </div>

    </div>

</x-filament-panels::page>