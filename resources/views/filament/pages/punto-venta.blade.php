@php use Illuminate\Support\Str; @endphp
<x-filament-panels::page>
<div class="space-y-4">

    {{-- ── Banner sucursal ── --}}
    <div class="fi-section rounded-xl p-4 flex justify-between items-center
                bg-gradient-to-r from-primary-700 to-primary-600 text-white shadow">
        <div class="flex items-center gap-3">
            <div class="bg-white/20 p-2 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div>
                <p class="text-xs opacity-75">Sucursal</p>
                <p class="font-bold">{{ $this->sucursalActual?->nombre ?? 'No asignada' }}</p>
            </div>
        </div>
        <div class="text-right">
            <p class="text-xs opacity-75">Atendido por</p>
            <p class="font-bold">{{ auth()->user()->name }}</p>
        </div>
    </div>

    {{-- ── Grid principal ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- ══ IZQUIERDA: Buscador + Productos ══ --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Buscador --}}
            <x-filament::section>
                <x-slot name="heading">
                    <span class="flex items-center gap-2 text-sm font-semibold">
                        <x-heroicon-m-magnifying-glass class="w-4 h-4 text-primary-500"/>
                        Buscar Producto
                    </span>
                </x-slot>

                <input
                    type="search"
                    wire:model.live.debounce.300ms="searchTerm"
                    placeholder="Nombre, código o SKU del producto..."
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600
                           bg-white dark:bg-gray-800 dark:text-gray-100
                           px-4 py-2.5 text-sm
                           focus:outline-none focus:ring-2 focus:ring-primary-500
                           placeholder-gray-400 dark:placeholder-gray-500"
                />

                {{-- Resultados --}}
                @if(strlen($searchTerm ?? '') >= 2)
                    <div class="mt-3">
                        @if(count($this->productos) > 0)
                            <p class="text-xs text-gray-400 dark:text-gray-500 mb-2">
                                {{ count($this->productos) }} resultado(s)
                            </p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-80 overflow-y-auto pr-1">
                                @foreach($this->productos as $item)
                                    <div wire:click="agregarProducto({{ $item->producto_id ?? $item->id }})"
                                         class="flex justify-between items-center
                                                rounded-lg border border-gray-200 dark:border-gray-700
                                                bg-gray-50 dark:bg-gray-800/60
                                                hover:border-primary-400 hover:bg-primary-50
                                                dark:hover:border-primary-500 dark:hover:bg-primary-900/20
                                                p-3 cursor-pointer transition-all group">
                                        <div class="min-w-0">
                                            <p class="font-medium text-sm text-gray-800 dark:text-gray-200 truncate">
                                                {{ Str::limit($item->producto->nombre ?? $item->nombre, 30) }}
                                            </p>
                                            <p class="text-xs text-gray-400 mt-0.5">
                                                {{ $item->producto->codigo ?? $item->codigo }}
                                                · Stock:
                                                <span @class([
                                                    'font-semibold',
                                                    'text-danger-500' => ($item->stock_actual ?? $item->stock_actual) <= 5,
                                                    'text-success-600 dark:text-success-400' => ($item->stock_actual ?? $item->stock_actual) > 5,
                                                ])>{{ $item->stock_actual }}</span>
                                            </p>
                                        </div>
                                        <span class="font-bold text-primary-600 dark:text-primary-400
                                                     text-sm ml-3 flex-shrink-0">
                                            ${{ number_format($item->producto->precio_venta ?? $item->precio_venta, 2) }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8 text-gray-400">
                                <x-heroicon-m-face-frown class="w-10 h-10 mx-auto mb-2 opacity-40"/>
                                <p class="text-sm">Sin resultados para "{{ $searchTerm }}"</p>
                            </div>
                        @endif
                    </div>
                @endif
            </x-filament::section>

            {{-- Productos destacados (solo sin búsqueda activa) --}}
            @if(empty($searchTerm) || strlen($searchTerm) < 2)
                <x-filament::section>
                    <x-slot name="heading">
                        <span class="flex items-center gap-2 text-sm font-semibold">
                            <x-heroicon-m-star class="w-4 h-4 text-warning-500"/>
                            Productos Destacados
                        </span>
                    </x-slot>

                    @if($this->productosRapidos->isEmpty())
                        <div class="text-center py-8 text-gray-400">
                            <x-heroicon-m-archive-box-x-mark class="w-10 h-10 mx-auto mb-2 opacity-40"/>
                            <p class="text-sm">Sin productos disponibles</p>
                        </div>
                    @else
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                            @foreach($this->productosRapidos as $item)
                                <button
                                    wire:click="agregarProducto({{ $item->producto_id ?? $item->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="agregarProducto({{ $item->producto_id ?? $item->id }})"
                                    class="group text-left rounded-xl border
                                           border-gray-200 dark:border-gray-700
                                           bg-white dark:bg-gray-800
                                           hover:border-primary-400 dark:hover:border-primary-500
                                           hover:bg-primary-50 dark:hover:bg-primary-900/20
                                           p-3 transition-all shadow-sm hover:shadow-md
                                           disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <p class="font-semibold text-sm text-gray-800 dark:text-gray-200
                                              leading-tight group-hover:text-primary-700
                                              dark:group-hover:text-primary-400">
                                        {{ Str::limit($item->producto->nombre ?? $item->nombre, 26) }}
                                    </p>
                                    <p class="text-xs text-gray-400 mt-1 mb-2">
                                        {{ $item->producto->codigo ?? $item->codigo }}
                                    </p>
                                    <div class="flex items-end justify-between">
                                        <span class="font-bold text-primary-600 dark:text-primary-400 text-base">
                                            ${{ number_format($item->producto->precio_venta ?? $item->precio_venta, 2) }}
                                        </span>
                                        <span @class([
                                            'text-xs font-medium px-1.5 py-0.5 rounded-full',
                                            'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400'
                                                => $item->stock_actual > 5,
                                            'bg-warning-100 text-warning-700 dark:bg-warning-900/30 dark:text-warning-400'
                                                => $item->stock_actual <= 5 && $item->stock_actual > 0,
                                            'bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-400'
                                                => $item->stock_actual <= 0,
                                        ])>
                                            {{ $item->stock_actual }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-primary-600 dark:text-primary-400
                                              opacity-0 group-hover:opacity-100 transition mt-1.5 font-medium">
                                        + Agregar al carrito
                                    </p>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </x-filament::section>
            @endif
        </div>

        <div class="space-y-4">

            {{-- Cliente --}}
            <x-filament::section>
                <x-slot name="heading">
                    <span class="flex items-center gap-2 text-sm font-semibold">
                        <x-heroicon-m-user class="w-4 h-4 text-info-500"/> Cliente
                    </span>
                </x-slot>

                <select
                    wire:model.live="cliente_id"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600
                           bg-white dark:bg-gray-800 dark:text-gray-100
                           px-3 py-2 text-sm
                           focus:outline-none focus:ring-2 focus:ring-primary-500"
                >
                    <option value="">— Seleccionar cliente —</option>
                    @foreach($this->clientes as $cliente)
                        <option value="{{ $cliente->id }}">{{ $cliente->nombre }}</option>
                    @endforeach
                </select>
            </x-filament::section>

            {{-- Carrito --}}
            <x-filament::section class="sticky top-4">
                {{-- Header del carrito --}}
                <x-slot name="heading">
                    <div class="flex items-center justify-between w-full">
                        <span class="flex items-center gap-2 text-sm font-semibold">
                            <x-heroicon-m-shopping-cart class="w-4 h-4"/> Carrito
                        </span>
                        @if($this->itemsCount > 0)
                            <span class="bg-primary-500 text-white text-xs font-bold
                                         px-2 py-0.5 rounded-full">
                                {{ $this->itemsCount }}
                            </span>
                        @endif
                    </div>
                </x-slot>

                {{-- Lista de items --}}
                <div class="divide-y divide-gray-100 dark:divide-gray-700 max-h-80 overflow-y-auto -mx-1 px-1">
                    @forelse($items as $productoId => $item)
                        <div class="py-3">
                            <div class="flex justify-between items-start gap-2">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium truncate dark:text-gray-200">
                                        {{ Str::limit($item['nombre'], 24) }}
                                    </p>
                                    <p class="text-xs text-gray-400">
                                        ${{ number_format($item['precio'], 2) }} c/u
                                    </p>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <p class="text-sm font-bold text-primary-600 dark:text-primary-400">
                                        ${{ number_format($item['subtotal'], 2) }}
                                    </p>
                                    <button wire:click="eliminarProducto({{ $item['id'] }})"
                                            class="text-xs text-danger-400 hover:text-danger-600 transition">
                                        eliminar
                                    </button>
                                </div>
                            </div>

                            <div class="flex items-center justify-between mt-2">
                                {{-- Controles cantidad --}}
                                <div class="flex items-center gap-1.5">
                                    <button wire:click="actualizarCantidad({{ $item['id'] }}, {{ $item['cantidad'] - 1 }})"
                                            class="w-6 h-6 rounded-full bg-gray-100 dark:bg-gray-700
                                                   hover:bg-gray-200 dark:hover:bg-gray-600
                                                   flex items-center justify-center
                                                   text-gray-700 dark:text-gray-200
                                                   text-sm font-bold transition">
                                        −
                                    </button>
                                    <span class="w-7 text-center text-sm font-semibold dark:text-gray-200">
                                        {{ $item['cantidad'] }}
                                    </span>
                                    <button wire:click="actualizarCantidad({{ $item['id'] }}, {{ $item['cantidad'] + 1 }})"
                                            class="w-6 h-6 rounded-full bg-gray-100 dark:bg-gray-700
                                                   hover:bg-gray-200 dark:hover:bg-gray-600
                                                   flex items-center justify-center
                                                   text-gray-700 dark:text-gray-200
                                                   text-sm font-bold transition">
                                        +
                                    </button>
                                </div>

                                {{-- Stock disponible --}}
                                @php
                                    $stock = \App\Models\InventarioAlmacen::where('producto_id', $item['id'])
                                        ->where('almacen_id', auth()->user()->almacen_id ?? 0)
                                        ->value('stock_actual') ?? 0;
                                @endphp
                                <span class="text-xs text-gray-400">
                                    Disp: <strong>{{ $stock }}</strong>
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="py-10 text-center text-gray-400">
                            <p class="text-sm font-medium">Carrito vacío</p>
                            <p class="text-xs mt-1 opacity-75">Agrega productos para comenzar</p><br>
                        </div>
                    @endforelse
                </div>

                {{-- Totales --}}
                @if($this->itemsCount > 0)
                    <div class="border-t border-gray-100 dark:border-gray-700 pt-3 mt-2 space-y-1.5">
                        <div class="flex justify-between text-sm text-gray-500 dark:text-gray-400">
                            <span>Subtotal</span>
                            <span>${{ number_format($this->total, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-500 dark:text-gray-400">
                            <span>IVA (13%)</span>
                            <span>${{ number_format($this->iva, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-lg font-bold
                                    text-primary-600 dark:text-primary-400
                                    pt-2 border-t border-gray-200 dark:border-gray-600">
                            <span>TOTAL</span>
                            <span>${{ number_format($this->totalConIva, 2) }}</span>
                        </div>
                    </div>
                @endif

                {{-- Botones --}}
                <div class="flex gap-2 mt-4">
                    <x-filament::button
                        wire:click="limpiarCarrito"
                        color="gray"
                        size="sm"
                        icon="heroicon-m-trash"
                        class="flex-1"
                    >
                        Limpiar
                    </x-filament::button>

                    <x-filament::button
                        wire:click="procesarVenta"
                        wire:loading.attr="disabled"
                        wire:target="procesarVenta"
                        color="success"
                        size="sm"
                        icon="heroicon-m-check-circle"
                        class="flex-1"
                        :disabled="empty($items) || !$cliente_id"
                    >
                        <span wire:loading.remove wire:target="procesarVenta">Cobrar</span>
                        <span wire:loading wire:target="procesarVenta">Procesando…</span>
                    </x-filament::button>
                </div>

                {{-- Avisos --}}
                @if(empty($items))
                    <p class="text-xs text-center text-gray-400 mt-2">
                        Agrega productos al carrito
                    </p>
                @elseif(!$cliente_id)
                    <p class="text-xs text-center text-warning-500 mt-2">
                        Selecciona un cliente para cobrar
                    </p>
                @endif
            </x-filament::section>

        </div>
    </div>
</div>
</x-filament-panels::page>