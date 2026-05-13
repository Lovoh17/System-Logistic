<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @foreach($this->getStats() as $stat)
                <a href="{{ $stat->getUrl() }}" class="block">
                    <div class="bg-white rounded-lg shadow p-4 hover:shadow-lg transition">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-sm text-gray-500">{{ $stat->getLabel() }}</p>
                                <p class="text-2xl font-bold">{{ $stat->getValue() }}</p>
                            </div>
                            <div class="text-3xl">{!! $stat->getIcon() !!}</div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Pedidos de Compra Recientes -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="bg-blue-600 px-4 py-3">
                    <h3 class="text-white font-semibold">📦 Pedidos de Compra Pendientes</h3>
                </div>
                <div class="p-4">
                    @if($this->getPedidosCompraRecientes()->count() > 0)
                        <div class="space-y-3">
                            @foreach($this->getPedidosCompraRecientes() as $pedido)
                                <div class="border rounded-lg p-3 hover:bg-gray-50">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <span class="font-semibold">{{ $pedido->numero }}</span>
                                            <div class="text-sm text-gray-500">{{ $pedido->proveedor->nombre }}</div>
                                        </div>
                                        <div class="text-right">
                                            <span class="inline-block px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">
                                                {{ ucfirst($pedido->estado) }}
                                            </span>
                                            <div class="text-xs text-gray-400 mt-1">
                                                Entrega: {{ $pedido->fecha_requerida?->format('d/m/Y') ?? 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-center text-gray-500 py-4">No hay pedidos pendientes</p>
                    @endif
                </div>
            </div>

            <!-- Envíos Recientes -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="bg-blue-600 px-4 py-3">
                    <h3 class="text-white font-semibold">🚚 Envíos en Tránsito</h3>
                </div>
                <div class="p-4">
                    @if($this->getEnviosRecientes()->count() > 0)
                        <div class="space-y-3">
                            @foreach($this->getEnviosRecientes() as $envio)
                                <div class="border rounded-lg p-3 hover:bg-gray-50">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <span class="font-semibold">Envío {{ $envio->numero }}</span>
                                            <div class="text-sm text-gray-500">{{ $envio->pedidoVenta->cliente->nombre ?? 'N/A' }}</div>
                                        </div>
                                        <div class="text-right">
                                            <span class="inline-block px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">
                                                {{ ucfirst($envio->estado) }}
                                            </span>
                                            <div class="text-xs text-gray-400 mt-1">
                                                Transportista: {{ $envio->transportista->nombre ?? 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-center text-gray-500 py-4">No hay envíos en tránsito</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Widgets adicionales -->
        <div class="grid grid-cols-1 gap-6">
            <livewire:filament.logistica.widgets.mapa-transportistas />
        </div>
    </div>
</x-filament-panels::page>