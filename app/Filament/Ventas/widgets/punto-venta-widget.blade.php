<div x-data="{
    carrito: @entangle('carrito'),
    total: @entangle('total'),
    totalConIva: @entangle('totalConIva'),
    iva: @entangle('iva'),
    cliente_id: @entangle('cliente_id'),
    busqueda: @entangle('busqueda'),
    resultadosBusqueda: @entangle('productosBusqueda'),
    clientes: @entangle('clientes'),
    productosRapidos: @entangle('productosRapidos'),
    mostrarTicket: false,
    ticketHtml: '',
}" x-on:carrito-actualizado.window="carrito = $wire.carrito; total = $wire.total"
  x-on:open-ticket-modal.window="mostrarTicket = true; ticketHtml = $event.detail.html"
  class="space-y-4">
    
    <!-- Modal del ticket -->
    <div x-show="mostrarTicket" x-cloak class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 overflow-hidden">
            <div x-html="ticketHtml"></div>
            <div class="p-4 border-t flex gap-2">
                <button @click="window.print()" class="flex-1 bg-green-700 text-white py-2 rounded-lg hover:bg-green-800">
                    🖨️ Imprimir
                </button>
                <button @click="mostrarTicket = false" class="flex-1 bg-gray-500 text-white py-2 rounded-lg hover:bg-gray-600">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Panel izquierdo: Productos -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow p-4">
            <h3 class="text-lg font-bold mb-4">🔍 Buscar Producto</h3>
            
            <input type="text" x-model="busqueda" @input.debounce.300ms="$wire.set('busqueda', busqueda)"
                   placeholder="Escanear o buscar por código, SKU o nombre..."
                   class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-primary">
            
            <div x-show="resultadosBusqueda.length > 0 && busqueda.length > 1" 
                 x-cloak class="mt-2 border rounded-lg max-h-60 overflow-y-auto">
                <template x-for="producto in resultadosBusqueda" :key="producto.id">
                    <div @click="$wire.set('producto_id', producto.id); $wire.agregarProducto(); busqueda = ''"
                         class="p-3 hover:bg-gray-100 cursor-pointer border-b">
                        <div class="font-semibold" x-text="producto.nombre"></div>
                        <div class="text-sm text-gray-500">
                            <span x-text="producto.codigo"></span> | 
                            Stock: <span x-text="producto.stock_actual"></span> | 
                            $<span x-text="producto.precio_venta"></span>
                        </div>
                    </div>
                </template>
            </div>
            
            <h3 class="font-semibold mt-6 mb-3">📦 Productos destacados</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <template x-for="producto in productosRapidos" :key="producto.id">
                    <button @click="$wire.set('producto_id', producto.id); $wire.agregarProducto()"
                            class="bg-gray-100 hover:bg-green-100 p-3 rounded-lg text-center transition">
                        <div class="font-semibold text-sm" x-text="producto.nombre.substring(0, 20)"></div>
                        <div class="text-green-700 font-bold">$<span x-text="producto.precio_venta.toFixed(2)"></span></div>
                        <div class="text-xs text-gray-500">Stock: <span x-text="producto.stock_actual"></span></div>
                    </button>
                </template>
            </div>
        </div>
        
        <!-- Panel derecho: Cliente y carrito -->
        <div class="space-y-4">
            <!-- Selección de cliente -->
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="font-bold mb-3">👤 Cliente</h3>
                <select x-model="cliente_id" class="w-full p-2 border rounded-lg">
                    <option value="">Seleccionar cliente...</option>
                    <template x-for="cliente in clientes" :key="cliente.id">
                        <option :value="cliente.id" x-text="cliente.nombre"></option>
                    </template>
                </select>
                <div class="mt-2 text-right">
                    <a href="/admin/clientes/create" target="_blank" class="text-sm text-primary hover:underline">
                        + Crear nuevo cliente
                    </a>
                </div>
            </div>
            
            <!-- Carrito -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-4 border-b">
                    <h3 class="font-bold">🛒 Carrito</h3>
                </div>
                
                <div class="max-h-80 overflow-y-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="p-2 text-left">Producto</th>
                                <th class="p-2 text-center">Cant</th>
                                <th class="p-2 text-right">Subtotal</th>
                                <th class="p-2"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(item, key) in carrito" :key="key">
                                <tr class="border-b">
                                    <td class="p-2" x-text="item.nombre.substring(0, 15)"></td>
                                    <td class="p-2 text-center">
                                        <input type="number" x-model="item.cantidad" 
                                               @change="$wire.actualizarCantidad(item.id, item.cantidad)"
                                               min="1" class="w-16 p-1 text-center border rounded">
                                    </td>
                                    <td class="p-2 text-right">$<span x-text="item.subtotal.toFixed(2)"></span></td>
                                    <td class="p-2 text-center">
                                        <button @click="$wire.eliminarProducto(item.id)" class="text-red-500">🗑️</button>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="Object.keys(carrito).length === 0">
                                <td colspan="4" class="p-6 text-center text-gray-400">Carrito vacío</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="p-4 border-t bg-gray-50">
                    <div class="flex justify-between text-sm mb-1">
                        <span>Subtotal:</span>
                        <span>$<span x-text="total.toFixed(2)"></span></span>
                    </div>
                    <div class="flex justify-between text-sm mb-1">
                        <span>IVA (13%):</span>
                        <span>$<span x-text="iva.toFixed(2)"></span></span>
                    </div>
                    <div class="flex justify-between text-lg font-bold text-green-700 pt-2 border-t">
                        <span>TOTAL:</span>
                        <span>$<span x-text="totalConIva.toFixed(2)"></span></span>
                    </div>
                    <div class="flex gap-2 mt-4">
                        <button @click="$wire.limpiarCarrito()" class="flex-1 bg-gray-500 text-white py-2 rounded-lg hover:bg-gray-600">
                            Limpiar
                        </button>
                        <button @click="$wire.procesarVenta()" class="flex-1 bg-green-700 text-white py-2 rounded-lg hover:bg-green-800">
                            ✅ Cobrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        // Configuración adicional si es necesaria
    });
</script>
@endpush