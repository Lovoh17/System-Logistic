<x-filament-panels::page>
    <!-- Cabecera con resumen -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-primary-50 rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-primary-700">{{ $this->resumen['total_productos'] }}</div>
            <div class="text-sm text-gray-600">Total Productos</div>
        </div>
        <div class="bg-danger-50 rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-danger-700">{{ $this->resumen['sin_stock'] }}</div>
            <div class="text-sm text-gray-600">Sin Stock</div>
        </div>
        <div class="bg-warning-50 rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-warning-700">{{ $this->resumen['stock_bajo'] }}</div>
            <div class="text-sm text-gray-600">Stock Bajo</div>
        </div>
        <div class="bg-info-50 rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-info-700">{{ $this->resumen['stock_alto'] }}</div>
            <div class="text-sm text-gray-600">Stock Alto</div>
        </div>
        <div class="bg-success-50 rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-success-700">${{ number_format($this->resumen['valor_inventario'], 2) }}</div>
            <div class="text-sm text-gray-600">Valor Inventario</div>
        </div>
    </div>

    <!-- Información de la sucursal -->
    <div class="bg-gray-100 rounded-lg p-4 mb-6 flex justify-between items-center">
        <div class="flex items-center gap-2">
            <x-heroicon-o-building-storefront class="w-5 h-5 text-gray-600" />
            <span class="font-semibold">Sucursal:</span>
            <span class="text-gray-700">{{ $this->sucursalActual?->nombre ?? 'No asignada' }}</span>
        </div>
        <div class="text-sm text-gray-500">
            Última actualización: {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

    <!-- Tabla de inventario -->
    {{ $this->table }}
</x-filament-panels::page>