<x-filament-panels::page>
    <div class="space-y-4">
        <div class="flex gap-4">
            <div class="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-5 h-24">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Productos
                        </p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-0.5">
                            {{ count($recomendaciones) }}
                        </p>
                    </div>
                    <div class="p-2.5 rounded-xl bg-primary-100 dark:bg-primary-900/30">
                        <x-heroicon-m-shopping-bag class="w-6 h-6 text-primary-600 dark:text-primary-400"/>
                    </div>
                </div>
            </div>
            <div class="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-5 h-24">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Stock Crítico
                        </p>
                        <p class="text-2xl font-bold text-danger-600 dark:text-danger-400 mt-0.5">
                            {{ collect($recomendaciones)->filter(fn($r) => $r['stock_actual'] <= 0)->count() }}
                        </p>
                    </div>
                    <div class="p-2.5 rounded-xl bg-danger-100 dark:bg-danger-900/30">
                        <x-heroicon-m-exclamation-triangle class="w-6 h-6 text-danger-600 dark:text-danger-400"/>
                    </div>
                </div>
            </div>
            <div class="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-5 h-24">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Proveedores
                        </p>
                        <p class="text-2xl font-bold text-info-600 dark:text-info-400 mt-0.5">
                            {{ collect($recomendaciones)->pluck('proveedor')->unique()->count() }}
                        </p>
                    </div>
                    <div class="p-2.5 rounded-xl bg-info-100 dark:bg-info-900/30">
                        <x-heroicon-m-building-office class="w-6 h-6 text-info-600 dark:text-info-400"/>
                    </div>
                </div>
            </div>
            <div class="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-5 h-24">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Inversión Sugerida
                        </p>
                        <p class="text-2xl font-bold text-success-600 dark:text-success-400 mt-0.5">
                            ${{ number_format(collect($recomendaciones)->sum(fn($r) => $r['cant_sugerida'] * $r['precio']), 2) }}
                        </p>
                    </div>
                    <div class="p-2.5 rounded-xl bg-success-100 dark:bg-success-900/30">
                        <x-heroicon-m-currency-dollar class="w-6 h-6 text-success-600 dark:text-success-400"/>
                    </div>
                </div>
            </div>
        </div>
        <x-filament::section>
            <x-slot name="heading">
                <span class="flex items-center gap-2">
                    <x-heroicon-m-light-bulb class="w-4 h-4 text-warning-500"/>
                    Recomendaciones de Compra
                    <span class="bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400
                                 text-xs font-bold px-2 py-0.5 rounded-full">
                        {{ count($recomendaciones) }}
                    </span>
                </span>
            </x-slot>

            @if(count($recomendaciones) === 0)
                <div class="text-center py-10 text-gray-400">
                    <x-heroicon-m-check-circle class="w-10 h-10 mx-auto mb-2 opacity-30"/>
                    <p class="text-sm">Sin productos por debajo del stock mínimo</p>
                    <p class="text-xs text-gray-400 mt-1">
                        Todos los productos tienen stock suficiente o no tienen configurado stock mínimo.
                    </p>
                </div>
            @else
                @php $agrupadas = collect($recomendaciones)->groupBy('proveedor'); @endphp
                @foreach($agrupadas as $proveedor => $items)
                    <div class="mt-6 first:mt-0">

                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <div class="p-1.5 rounded-lg bg-info-100 dark:bg-info-900/30">
                                    <x-heroicon-m-building-office class="w-4 h-4 text-info-600 dark:text-info-400"/>
                                </div>
                                <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200">
                                    {{ $proveedor }}
                                </h3>
                                <span class="bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400
                     text-xs font-medium px-2 py-0.5 rounded-full">
            {{ $items->count() }} producto(s)
        </span>
                                @if($items->first()['proveedor_en_oc'])
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs
                         font-semibold bg-warning-100 text-warning-700
                         dark:bg-warning-900/30 dark:text-warning-400">
                <x-heroicon-m-exclamation-triangle class="w-3 h-3"/>
                OC pendiente: {{ implode(', ', $items->first()['oc_proveedor_nums']) }}
            </span>
                                @endif
                            </div>

                            @php
                                $todosEnOC = $items->every(fn($i) => $i['en_oc']);
                                $algunoEnOC = $items->some(fn($i) => $i['en_oc']);
                            @endphp

                            @if($todosEnOC)
                                {{-- Todos los productos ya están en OC --}}
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm
                     bg-warning-100 text-warning-700 dark:bg-warning-900/30
                     dark:text-warning-400 rounded-lg font-medium">
            <x-heroicon-m-clock class="w-4 h-4"/>
            Todos en OC pendiente
        </span>
                            @else
                                <x-filament::button
                                        wire:click="crearOCPorProveedor('{{ $proveedor }}')"
                                        wire:loading.attr="disabled"
                                        icon="heroicon-m-shopping-cart"
                                        color="primary"
                                        size="sm"
                                >
                                    @if($algunoEnOC)
                                        Crear OC (productos sin OC)
                                    @else
                                        Crear OC para {{ $proveedor }}
                                    @endif
                                </x-filament::button>
                            @endif
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <th class="text-left py-2.5 px-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Producto</th>
                                    <th class="text-right py-2.5 px-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Stock</th>
                                    <th class="text-right py-2.5 px-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Mínimo</th>
                                    <th class="text-right py-2.5 px-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Promedio/día</th>
                                    <th class="text-right py-2.5 px-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Sugerido</th>
                                    <th class="text-right py-2.5 px-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Precio</th>
                                    <th class="text-right py-2.5 px-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Total</th>
                                    <th class="text-center py-2.5 px-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Acción</th>
                                </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach($items as $recomendacion)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition
               {{ $recomendacion['en_oc'] ? 'opacity-60' : '' }}">
                                        <td class="py-3 px-3 font-medium text-gray-800 dark:text-gray-200">
                                            <div class="flex items-center gap-2">
                                                {{ $recomendacion['producto'] }}
                                                @if($recomendacion['en_oc'])
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs
                                 font-semibold bg-warning-100 text-warning-700
                                 dark:bg-warning-900/30 dark:text-warning-400">
                        <x-heroicon-m-clock class="w-3 h-3"/>
                        En {{ implode(', ', $recomendacion['oc_numeros']) }}
                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="py-3 px-3 text-right">
            <span @class([
                'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold',
                'bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-400'
                    => $recomendacion['stock_actual'] <= 0,
                'bg-warning-100 text-warning-700 dark:bg-warning-900/30 dark:text-warning-400'
                    => $recomendacion['stock_actual'] > 0,
            ])>
                {{ number_format($recomendacion['stock_actual'], 2) }}
            </span>
                                        </td>
                                        <td class="py-3 px-3 text-right text-gray-600 dark:text-gray-400">
                                            {{ number_format($recomendacion['stock_minimo'], 2) }}
                                        </td>
                                        <td class="py-3 px-3 text-right text-gray-600 dark:text-gray-400">
                                            {{ number_format($recomendacion['prom_diario'], 2) }}
                                        </td>
                                        <td class="py-3 px-3 text-right font-semibold text-primary-600 dark:text-primary-400">
                                            {{ number_format($recomendacion['cant_sugerida'], 2) }}
                                        </td>
                                        <td class="py-3 px-3 text-right text-gray-600 dark:text-gray-400">
                                            ${{ number_format($recomendacion['precio'], 2) }}
                                        </td>
                                        <td class="py-3 px-3 text-right font-semibold text-success-600 dark:text-success-400">
                                            ${{ number_format($recomendacion['cant_sugerida'] * $recomendacion['precio'], 2) }}
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            @if($recomendacion['en_oc'])
                                                {{-- Ya está en OC pendiente --}}
                                                <span class="inline-flex items-center gap-1 px-3 py-1.5 text-xs
                             bg-warning-100 text-warning-700 dark:bg-warning-900/30
                             dark:text-warning-400 rounded-lg font-medium">
                    <x-heroicon-m-clock class="w-3 h-3"/>
                    Pendiente recepción
                </span>
                                            @else
                                                <button
                                                        wire:click="crearOCProducto({{ $recomendacion['producto_id'] }}, {{ $recomendacion['cant_sugerida'] }}, '{{ $recomendacion['proveedor_id'] }}')"
                                                        wire:loading.attr="disabled"
                                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs
                           bg-primary-600 hover:bg-primary-700 text-white rounded-lg
                           font-medium transition disabled:opacity-50"
                                                >
                                                    <x-heroicon-m-plus class="w-3 h-3"/>
                                                    Agregar a OC
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                    </div>

                    @if(!$loop->last)
                        <div class="border-t border-gray-100 dark:border-gray-800 mt-6"></div>
                    @endif
                @endforeach
                <div class="mt-4 pt-3 border-t border-gray-200 dark:border-gray-700">
                    <p class="text-xs text-gray-400">
                        <span class="font-medium text-gray-500 dark:text-gray-400">Nota:</span>
                        Las cantidades sugeridas se calculan con base en el promedio de ventas de los últimos 30 días,
                        el tiempo de entrega del proveedor + 7 días de buffer, y el stock mínimo necesario.
                    </p>
                </div>
            @endif
        </x-filament::section>
    </div>
</x-filament-panels::page>