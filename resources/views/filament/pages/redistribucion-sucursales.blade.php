<x-filament-panels::page>
    <div class="space-y-4">

        <div class="flex gap-4">

            <div class="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-5 h-24">
                <div class="flex items-center justify-between h-full">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Sugerencias</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-0.5">{{ count($sugerencias) }}</p>
                    </div>
                    <div class="p-2.5 rounded-xl bg-primary-100 dark:bg-primary-900/30">
                        <x-heroicon-m-arrows-right-left class="w-6 h-6 text-primary-600 dark:text-primary-400"/>
                    </div>
                </div>
            </div>

            <div class="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-5 h-24">
                <div class="flex items-center justify-between h-full">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Sin Stock</p>
                        <p class="text-2xl font-bold text-danger-600 dark:text-danger-400 mt-0.5">{{ collect($sugerencias)->where('urgencia', 3)->count() }}</p>
                    </div>
                    <div class="p-2.5 rounded-xl bg-danger-100 dark:bg-danger-900/30">
                        <x-heroicon-m-exclamation-triangle class="w-6 h-6 text-danger-600 dark:text-danger-400"/>
                    </div>
                </div>
            </div>

            <div class="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-5 h-24">
                <div class="flex items-center justify-between h-full">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Sucursales</p>
                        <p class="text-2xl font-bold text-info-600 dark:text-info-400 mt-0.5">{{ count($almacenesMatriz) }}</p>
                    </div>
                    <div class="p-2.5 rounded-xl bg-info-100 dark:bg-info-900/30">
                        <x-heroicon-m-building-storefront class="w-6 h-6 text-info-600 dark:text-info-400"/>
                    </div>
                </div>
            </div>

            <div class="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-5 h-24">
                <div class="flex items-center justify-between h-full">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Costo Total</p>
                        <p class="text-2xl font-bold text-success-600 dark:text-success-400 mt-0.5">
                            ${{ number_format(collect($sugerencias)->sum('costo_estimado'), 2) }}
                        </p>
                    </div>
                    <div class="p-2.5 rounded-xl bg-success-100 dark:bg-success-900/30">
                        <x-heroicon-m-currency-dollar class="w-6 h-6 text-success-600 dark:text-success-400"/>
                    </div>
                </div>
            </div>

        </div>

        <x-filament::section>
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex flex-wrap gap-5">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-success-500 flex-shrink-0"></span>
                        <span class="text-sm text-gray-600 dark:text-gray-400">Óptimo</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-warning-500 flex-shrink-0"></span>
                        <span class="text-sm text-gray-600 dark:text-gray-400">Stock Bajo</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-danger-500 flex-shrink-0"></span>
                        <span class="text-sm text-gray-600 dark:text-gray-400">Sin Stock</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-primary-500 flex-shrink-0"></span>
                        <span class="text-sm text-gray-600 dark:text-gray-400">Sobrestock</span>
                    </div>
                </div>
                <div class="flex gap-2">
                    <x-filament::button
                            wire:click="recalcular"
                            wire:loading.attr="disabled"
                            icon="heroicon-m-arrow-path"
                            color="gray"
                            size="sm"
                    >
                        <span wire:loading.remove>Recalcular</span>
                        <span wire:loading>Calculando...</span>
                    </x-filament::button>
                    <x-filament::button
                            wire:click="toggleMatriz"
                            icon="heroicon-m-table-cells"
                            color="gray"
                            size="sm"
                    >
                        {{ $mostrarMatriz ? 'Ocultar matriz' : 'Ver matriz' }}
                    </x-filament::button>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">
                <span class="flex items-center gap-2">
                    <x-heroicon-m-arrows-right-left class="w-4 h-4 text-primary-500"/>
                    Sugerencias de Redistribución
                    <span class="bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400
                                 text-xs font-bold px-2 py-0.5 rounded-full">
                        {{ count($sugerencias) }}
                    </span>
                </span>
            </x-slot>

            @if(count($sugerencias) === 0)
                <div class="text-center py-10 text-gray-400">
                    <x-heroicon-m-check-circle class="w-10 h-10 mx-auto mb-2 opacity-30"/>
                    <p class="text-sm">Sin desequilibrios detectados</p>
                    <p class="text-xs text-gray-400 mt-1">
                        Todos los productos están dentro de sus rangos mínimo–máximo,
                        o las sucursales no tienen coordenadas configuradas.
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="text-left py-2.5 px-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Urgencia</th>
                            <th class="text-left py-2.5 px-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Producto</th>
                            <th class="text-left py-2.5 px-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Origen → Destino</th>
                            <th class="text-right py-2.5 px-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Cantidad</th>
                            <th class="text-right py-2.5 px-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Distancia</th>
                            <th class="text-right py-2.5 px-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Costo</th>
                            <th class="text-center py-2.5 px-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Acción</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($sugerencias as $i => $sug)
                            <tr class="hover:bg-gray-100/60 dark:hover:bg-white/5 transition-colors">
                                <td class="py-3 px-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                                                     {{ $this->urgenciaClase($sug['urgencia']) }}">
                                            {{ $this->urgenciaLabel($sug['urgencia']) }}
                                        </span>
                                </td>
                                <td class="py-3 px-3 font-medium text-gray-800 dark:text-gray-200">
                                    {{ $sug['producto_nombre'] }}
                                </td>
                                <td class="py-3 px-3">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-gray-700 dark:text-gray-300">{{ $sug['origen_nombre'] }}</span>
                                        <x-heroicon-m-arrow-right class="w-3 h-3 text-gray-400 flex-shrink-0"/>
                                        <span class="text-gray-700 dark:text-gray-300">{{ $sug['destino_nombre'] }}</span>
                                    </div>
                                    <div class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                                        Stock origen: {{ number_format($sug['stock_origen'], 1) }}
                                        · Déficit: {{ number_format($sug['deficit'], 1) }}
                                    </div>
                                </td>
                                <td class="py-3 px-3 text-right font-semibold text-primary-600 dark:text-primary-400">
                                    {{ number_format($sug['cantidad_sugerida'], 1) }}
                                </td>
                                <td class="py-3 px-3 text-right {{ $this->distanciaClase($sug['distancia_km']) }}">
                                    {{ number_format($sug['distancia_km'], 2) }} km
                                </td>
                                <td class="py-3 px-3 text-right text-gray-600 dark:text-gray-400">
                                    ${{ number_format($sug['costo_estimado'], 2) }}
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <button wire:click="crearTraslado({{ $i }})"
                                            wire:loading.attr="disabled"
                                            wire:target="crearTraslado({{ $i }})"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs
                                                       bg-primary-600 hover:bg-primary-700 dark:bg-primary-500
                                                       dark:hover:bg-primary-600 text-white rounded-lg
                                                       font-medium transition disabled:opacity-50">
                                        <svg wire:loading wire:target="crearTraslado({{ $i }})"
                                             class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                                        </svg>
                                        <span wire:loading.remove wire:target="crearTraslado({{ $i }})">Crear</span>
                                        <span wire:loading wire:target="crearTraslado({{ $i }})">Creando...</span>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700 text-xs text-gray-400">
                    Costo estimado: $0.50/km · Distancia:
                    <span class="text-success-600 dark:text-success-400">≤10km</span> ·
                    <span class="text-warning-600 dark:text-warning-400">≤30km</span> ·
                    <span class="text-danger-600 dark:text-danger-400">&gt;30km</span>
                </div>
            @endif
        </x-filament::section>

        @if(count($almacenesMatriz) >= 2)
            <x-filament::section>
                <button wire:click="toggleMatriz" class="w-full flex items-center justify-between">
                    <span class="flex items-center gap-2">
                        <x-heroicon-m-map-pin class="w-4 h-4 text-primary-500"/>
                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                            Matriz de Distancias entre Sucursales (km)
                        </span>
                    </span>
                    @if($mostrarMatriz)
                        <x-heroicon-m-chevron-up class="w-4 h-4 text-gray-400"/>
                    @else
                        <x-heroicon-m-chevron-down class="w-4 h-4 text-gray-400"/>
                    @endif
                </button>

                @if($mostrarMatriz)
                    <div class="mt-4 overflow-x-auto">
                        <table class="text-xs border-collapse w-full">
                            <thead>
                            <tr>
                                <th class="border border-gray-200 dark:border-gray-700
                                               bg-gray-100 dark:bg-gray-700/60
                                               px-4 py-2 text-left text-gray-600 dark:text-gray-300 min-w-[130px]">
                                    Sucursal
                                </th>
                                @foreach($almacenesMatriz as $col)
                                    <th class="border border-gray-200 dark:border-gray-700
                                                   bg-gray-100 dark:bg-gray-700/60
                                                   px-4 py-2 text-center text-gray-600 dark:text-gray-300 min-w-[100px] font-medium">
                                        {{ $col['nombre'] }}
                                    </th>
                                @endforeach
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($almacenesMatriz as $row)
                                <tr class="hover:bg-gray-100/60 dark:hover:bg-white/5 transition-colors">
                                    <td class="border border-gray-200 dark:border-gray-700
                                                   bg-gray-50 dark:bg-gray-800/80
                                                   px-4 py-2 font-semibold text-gray-700 dark:text-gray-300">
                                        {{ $row['nombre'] }}
                                    </td>
                                    @foreach($almacenesMatriz as $col)
                                        @php
                                            $km = $matrizKm[$row['id']][$col['id']] ?? null;
                                            $esDiagonal = $row['id'] === $col['id'];
                                        @endphp
                                        <td @class([
                                                'border border-gray-200 dark:border-gray-700 px-4 py-2 text-center',
                                                'bg-gray-100 dark:bg-gray-800/80 text-gray-400 dark:text-gray-600' => $esDiagonal,
                                                'text-success-600 dark:text-success-400 font-semibold' => !$esDiagonal && $km !== null && $km <= 10,
                                                'text-warning-600 dark:text-warning-400'               => !$esDiagonal && $km !== null && $km > 10 && $km <= 30,
                                                'text-danger-600 dark:text-danger-400'                 => !$esDiagonal && $km !== null && $km > 30,
                                            ])>
                                            @if($esDiagonal) —
                                            @elseif($km !== null) {{ number_format($km, 1) }}
                                            @else <span class="text-gray-300 dark:text-gray-600">N/D</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        <p class="mt-3 text-xs text-gray-400">N/D: sucursal sin coordenadas configuradas.</p>
                    </div>
                @endif
            </x-filament::section>
        @else
            <x-filament::section>
                <div class="flex items-start gap-3">
                    <x-heroicon-m-exclamation-triangle class="w-5 h-5 text-warning-600 dark:text-warning-400 mt-0.5 flex-shrink-0"/>
                    <div>
                        <p class="font-medium text-warning-800 dark:text-warning-300">Coordenadas no configuradas</p>
                        <p class="text-sm text-warning-700 dark:text-warning-400 mt-0.5">
                            Configure latitud y longitud en al menos dos sucursales para recibir sugerencias.
                            Puede asignarlas desde <strong>Almacenes → Editar sucursal</strong>.
                        </p>
                    </div>
                </div>
            </x-filament::section>
        @endif

    </div>
</x-filament-panels::page>