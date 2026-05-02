<x-filament-panels::page>
    @php $m = $this->getMetricas(); @endphp

    {{-- Filtros de Período --}}
    <div class="flex gap-4 mb-6 flex-wrap items-end">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Desde</label>
            <input type="date" wire:model.live="fecha_desde"
                class="rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white px-3 py-2 text-sm" />
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Hasta</label>
            <input type="date" wire:model.live="fecha_hasta"
                class="rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white px-3 py-2 text-sm" />
        </div>
        <div class="flex gap-2">
            <button wire:click="$set('fecha_desde', '{{ now()->startOfMonth()->format('Y-m-d') }}')"
                class="px-3 py-2 text-xs bg-blue-100 hover:bg-blue-200 dark:bg-blue-900 dark:hover:bg-blue-800 text-blue-700 dark:text-blue-300 rounded-lg font-medium transition">
                Este mes
            </button>
            <button wire:click="$set('fecha_desde', '{{ now()->subDays(30)->format('Y-m-d') }}')"
                class="px-3 py-2 text-xs bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 rounded-lg font-medium transition">
                Últimos 30 días
            </button>
        </div>
    </div>

    {{-- KPIs Principales --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <div class="text-xs text-gray-500 uppercase tracking-wide font-semibold mb-1">Ventas del Período</div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white">${{ number_format($m['totalVentas'], 2) }}</div>
            <div class="text-xs text-gray-400 mt-1">{{ $m['pedidosTotales'] }} pedidos totales</div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <div class="text-xs text-gray-500 uppercase tracking-wide font-semibold mb-1">Tasa de Entrega</div>
            <div class="text-2xl font-bold {{ $m['tasaEntrega'] >= 90 ? 'text-emerald-600' : ($m['tasaEntrega'] >= 70 ? 'text-amber-500' : 'text-rose-600') }}">
                {{ $m['tasaEntrega'] }}%
            </div>
            <div class="text-xs text-gray-400 mt-1">{{ $m['pedidosEntregados'] }} de {{ $m['pedidosTotales'] }} entregados</div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <div class="text-xs text-gray-500 uppercase tracking-wide font-semibold mb-1">Tiempo Promedio Entrega</div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ $m['tiempoPromedio'] ? round($m['tiempoPromedio']) . 'h' : '—' }}
            </div>
            <div class="text-xs text-gray-400 mt-1">desde despacho hasta entrega</div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <div class="text-xs text-gray-500 uppercase tracking-wide font-semibold mb-1">Valor de Inventario</div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white">${{ number_format($m['valorInventario'], 2) }}</div>
            <div class="text-xs {{ $m['productosSinStock'] > 0 ? 'text-rose-500' : 'text-gray-400' }} mt-1">
                {{ $m['productosSinStock'] }} sin stock · {{ $m['productosStockBajo'] }} bajo mínimo
            </div>
        </div>
    </div>

    {{-- Segunda fila de KPIs --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border p-4 text-center">
            <div class="text-3xl font-bold text-blue-600">{{ $m['totalEnvios'] }}</div>
            <div class="text-xs text-gray-500 mt-1">Total Envíos</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border p-4 text-center">
            <div class="text-3xl font-bold text-emerald-600">{{ $m['enviosEntregados'] }}</div>
            <div class="text-xs text-gray-500 mt-1">Envíos Entregados</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border p-4 text-center">
            <div class="text-3xl font-bold text-rose-500">{{ $m['enviosFallidos'] }}</div>
            <div class="text-xs text-gray-500 mt-1">Envíos Fallidos</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border p-4 text-center">
            <div class="text-3xl font-bold text-amber-500">{{ $m['pedidosCancelados'] }}</div>
            <div class="text-xs text-gray-500 mt-1">Pedidos Cancelados</div>
        </div>
    </div>

    {{-- Tablas Top --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- Top Clientes --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                🏆 Top 5 Clientes del Período
            </h3>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs text-gray-500 border-b dark:border-gray-700">
                        <th class="text-left pb-2">#</th>
                        <th class="text-left pb-2">Cliente</th>
                        <th class="text-center pb-2">Pedidos</th>
                        <th class="text-right pb-2">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($m['topClientes'] as $i => $c)
                    <tr class="border-b dark:border-gray-700 last:border-0">
                        <td class="py-2 text-gray-400 font-bold">{{ $i + 1 }}</td>
                        <td class="py-2 font-medium dark:text-gray-200">{{ $c->cliente?->nombre ?? '—' }}</td>
                        <td class="py-2 text-center">
                            <span class="bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 text-xs font-semibold px-2 py-0.5 rounded-full">
                                {{ $c->num_pedidos }}
                            </span>
                        </td>
                        <td class="py-2 text-right font-semibold text-emerald-600">${{ number_format($c->total_comprado, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="py-4 text-center text-gray-400 text-xs">Sin datos en este período</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Top Productos --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                📦 Top 5 Productos Más Vendidos
            </h3>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs text-gray-500 border-b dark:border-gray-700">
                        <th class="text-left pb-2">#</th>
                        <th class="text-left pb-2">Producto</th>
                        <th class="text-center pb-2">Unidades</th>
                        <th class="text-right pb-2">Valor</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($m['topProductos'] as $i => $p)
                    <tr class="border-b dark:border-gray-700 last:border-0">
                        <td class="py-2 text-gray-400 font-bold">{{ $i + 1 }}</td>
                        <td class="py-2 font-medium dark:text-gray-200">{{ $p->producto?->nombre ?? '—' }}</td>
                        <td class="py-2 text-center">
                            <span class="bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300 text-xs font-semibold px-2 py-0.5 rounded-full">
                                {{ number_format($p->total_vendido, 0) }}
                            </span>
                        </td>
                        <td class="py-2 text-right font-semibold text-emerald-600">${{ number_format($p->total_valor, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="py-4 text-center text-gray-400 text-xs">Sin datos en este período</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-filament-panels::page>
