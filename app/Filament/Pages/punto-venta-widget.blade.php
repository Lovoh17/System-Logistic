<x-filament-panels::page>
    @php $m = $this->getMetricas(); @endphp

    {{-- ── Saludo ── --}}
    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">
            👋 Bienvenido, {{ auth()->user()->name }}
        </h2>
        <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ now()->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
        </p>
    </div>

    {{-- ── KPIs principales ── --}}
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <div class="flex items-center justify-between mb-1">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Ventas Hoy</span>
                <span class="text-emerald-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
            </div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white">${{ number_format($m['ventas_hoy'], 2) }}</div>
            <div class="text-xs text-gray-400 mt-1">{{ $m['pedidos_hoy'] }} pedidos registrados</div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <div class="flex items-center justify-between mb-1">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Pendientes</span>
                <span class="text-amber-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
            </div>
            <div class="text-2xl font-bold {{ $m['pendientes'] > 5 ? 'text-amber-500' : 'text-gray-900 dark:text-white' }}">
                {{ $m['pendientes'] }}
            </div>
            <div class="text-xs text-gray-400 mt-1">pedidos por procesar</div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <div class="flex items-center justify-between mb-1">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Entregados Hoy</span>
                <span class="text-blue-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </span>
            </div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $m['entregados_hoy'] }}</div>
            <div class="text-xs text-gray-400 mt-1">entregas completadas</div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <div class="flex items-center justify-between mb-1">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Ventas del Mes</span>
                <span class="text-purple-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </span>
            </div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white">${{ number_format($m['ventas_mes'], 2) }}</div>
            <div class="text-xs text-gray-400 mt-1">{{ now()->isoFormat('MMMM YYYY') }}</div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <div class="flex items-center justify-between mb-1">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Clientes Activos</span>
                <span class="text-indigo-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </span>
            </div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $m['clientes_activos'] }}</div>
            <div class="text-xs text-gray-400 mt-1">registrados en el sistema</div>
        </div>

        {{-- Acceso rápido nuevo pedido --}}
        <a href="{{ route('filament.ventas.resources.pedido-ventas.create') }}"
           class="bg-emerald-600 hover:bg-emerald-700 transition rounded-xl shadow-sm p-5 flex flex-col items-center justify-center text-white cursor-pointer">
            <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span class="text-sm font-bold">Nuevo Pedido</span>
        </a>

    </div>

    {{-- ── Últimos pedidos del día ── --}}
    @php
        $ultimosPedidos = \App\Models\PedidoVenta::with('cliente')
            ->whereDate('fecha_pedido', today())
            ->latest()
            ->limit(8)
            ->get();
    @endphp

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
        <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            Pedidos de Hoy
        </h3>

        @if ($ultimosPedidos->isEmpty())
            <p class="text-center text-sm text-gray-400 py-6">No hay pedidos registrados hoy.</p>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs text-gray-500 border-b dark:border-gray-700">
                        <th class="text-left pb-2">N° Pedido</th>
                        <th class="text-left pb-2">Cliente</th>
                        <th class="text-center pb-2">Estado</th>
                        <th class="text-right pb-2">Total</th>
                        <th class="text-right pb-2"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($ultimosPedidos as $pedido)
                    <tr class="border-b dark:border-gray-700 last:border-0 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="py-2">
                            <span class="font-mono text-xs bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 px-2 py-0.5 rounded">
                                {{ $pedido->numero }}
                            </span>
                        </td>
                        <td class="py-2 font-medium dark:text-gray-200">
                            {{ $pedido->cliente?->nombre ?? '—' }}
                        </td>
                        <td class="py-2 text-center">
                            @php
                                $colores = [
                                    'borrador'       => 'bg-gray-100 text-gray-600',
                                    'confirmado'     => 'bg-blue-100 text-blue-700',
                                    'en_preparacion' => 'bg-amber-100 text-amber-700',
                                    'listo'          => 'bg-purple-100 text-purple-700',
                                    'en_transito'    => 'bg-indigo-100 text-indigo-700',
                                    'entregado'      => 'bg-emerald-100 text-emerald-700',
                                    'cancelado'      => 'bg-red-100 text-red-700',
                                ];
                                $color = $colores[$pedido->estado] ?? 'bg-gray-100 text-gray-600';
                            @endphp
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $color }}">
                                {{ ucfirst(str_replace('_', ' ', $pedido->estado)) }}
                            </span>
                        </td>
                        <td class="py-2 text-right font-semibold text-emerald-600">
                            ${{ number_format($pedido->total, 2) }}
                        </td>
                        <td class="py-2 text-right">
                            <a href="{{ route('filament.ventas.resources.pedido-ventas.view', $pedido) }}"
                               class="text-xs text-blue-500 hover:text-blue-700">Ver →</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

</x-filament-panels::page>