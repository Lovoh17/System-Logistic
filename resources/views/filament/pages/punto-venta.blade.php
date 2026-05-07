<x-filament-panels::page>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Panel izquierdo: Productos -->
        <div class="lg:col-span-2 space-y-4">
            <!-- Buscador de productos -->
            <x-filament::card>
                <x-slot name="heading">
                    🔍 Buscar Producto
                </x-slot>
                
                <div class="space-y-4">
                    <div>
                        <x-filament::input.wrapper>
                            <x-filament::input 
                                type="search" 
                                wire:model.live.debounce.300ms="searchTerm"
                                placeholder="Escribe el nombre, código o SKU del producto..."
                                class="w-full" />
                        </x-filament::input.wrapper>
                    </div>
                    
                    @if(count($this->productos) > 0)
                        <div class="border rounded-lg overflow-hidden max-h-60 overflow-y-auto">
                            @foreach($this->productos as $producto)
                                <div wire:click="agregarProducto({{ $producto->id }})"
                                     class="p-3 hover:bg-gray-100 cursor-pointer border-b flex justify-between items-center">
                                    <div>
                                        <div class="font-semibold">{{ $producto->nombre }}</div>
                                        <div class="text-sm text-gray-500">
                                            Código: {{ $producto->codigo }} | Stock: {{ $producto->stock_actual }}
                                        </div>
                                    </div>
                                    <div class="text-green-700 font-bold">${{ number_format($producto->precio_venta, 2) }}</div>
                                </div>
                            @endforeach
                        </div>
                    @elseif(strlen($searchTerm) > 0)
                        <div class="text-center p-4 text-gray-500">No se encontraron productos</div>
                    @endif
                </div>
            </x-filament::card>
            
            <!-- Productos destacados -->
            <div>
                <h3 class="text-lg font-semibold mb-3">📦 Productos destacados</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    @foreach($this->productosRapidos as $producto)
                        <button wire:click="agregarProducto({{ $producto->id }})" 
                                class="bg-white hover:bg-green-50 p-4 rounded-xl shadow-sm text-center transition-all hover:shadow-md border border-gray-100">
                            <div class="font-semibold text-sm">
                                {{ \Illuminate\Support\Str::limit($producto->nombre, 25) }}
                            </div>
                            <div class="text-green-700 font-bold text-lg mt-2">
                                ${{ number_format($producto->precio_venta, 2) }}
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                Stock: {{ $producto->stock_actual }}
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
        
        <!-- Panel derecho: Cliente y carrito -->
        <div class="space-y-4">
            <!-- Selección de cliente -->
            <x-filament::card>
                <x-slot name="heading">
                    👤 Cliente
                </x-slot>
                
                <select wire:model="cliente_id" class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-primary-500">
                    <option value="">Seleccionar cliente...</option>
                    @foreach($this->clientes as $cliente)
                        <option value="{{ $cliente->id }}">{{ $cliente->nombre }}</option>
                    @endforeach
                </select>
            </x-filament::card>
            
            <!-- Carrito -->
            <x-filament::card>
                <x-slot name="heading" class="flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        🛒 Carrito de Compras
                    </div>
                    <span class="text-sm text-gray-500">{{ $this->itemsCount }} items</span>
                </x-slot>
                
                <div class="divide-y max-h-96 overflow-y-auto">
                    @forelse($items as $key => $item)
                    <div class="py-3 flex justify-between items-center">
                        <div class="flex-1">
                            <div class="font-medium">
                                {{ \Illuminate\Support\Str::limit($item['nombre'], 20) }}
                            </div>
                            <div class="text-sm text-gray-500">
                                ${{ number_format($item['precio'], 2) }} c/u
                            </div>
                        </div>
                        <div class="flex items-center gap-2 mr-4">
                            <button wire:click="actualizarCantidad({{ $item['id'] }}, {{ $item['cantidad'] - 1 }})"
                                    class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200">-</button>
                            <span class="w-8 text-center font-medium">{{ $item['cantidad'] }}</span>
                            <button wire:click="actualizarCantidad({{ $item['id'] }}, {{ $item['cantidad'] + 1 }})"
                                    class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200">+</button>
                        </div>
                        <div class="text-right">
                            <div class="font-semibold">${{ number_format($item['subtotal'], 2) }}</div>
                            <button wire:click="eliminarProducto({{ $item['id'] }})"
                                    class="text-red-500 text-sm hover:text-red-700">Eliminar</button>
                        </div>
                    </div>
                    @empty
                    <div class="py-8 text-center text-gray-400">
                        🛒 Carrito vacío
                    </div>
                    @endforelse
                </div>
                
                <div class="border-t pt-4 mt-4 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span>Subtotal:</span>
                        <span class="font-medium">${{ number_format($this->total, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span>IVA (13%):</span>
                        <span class="font-medium">${{ number_format($this->iva, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-lg font-bold text-green-700 pt-2 border-t">
                        <span>TOTAL:</span>
                        <span>${{ number_format($this->totalConIva, 2) }}</span>
                    </div>
                </div>
                
                <div class="flex gap-2 mt-4">
                    <button wire:click="limpiarCarrito" 
                            class="flex-1 bg-gray-500 text-white py-2 rounded-lg hover:bg-gray-600 transition">
                        🗑️ Limpiar
                    </button>
                    <button wire:click="procesarVenta" 
                            class="flex-1 bg-green-700 text-white py-2 rounded-lg hover:bg-green-800 transition">
                        ✅ Cobrar
                    </button>
                </div>
            </x-filament::card>
        </div>
    </div>
</x-filament-panels::page>