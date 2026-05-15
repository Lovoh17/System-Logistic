<div class="space-y-2">
    @forelse($stocks as $inv)
        <div class="p-3 border rounded-lg {{ $inv->stock_actual <= $inv->stock_minimo ? 'bg-danger-50' : 'bg-gray-50' }}">
            <strong class="text-lg">{{ $inv->almacen->nombre }}</strong><br>
            <span>
                📦 Stock actual: 
                <strong class="{{ $inv->stock_actual <= $inv->stock_minimo ? 'text-danger-600' : 'text-success-600' }}">
                    {{ $inv->stock_actual }} {{ $record->unidad_medida }}
                </strong>
            </span><br>
            <span class="text-sm text-gray-500">
                📉 Mínimo: {{ $inv->stock_minimo }} | 
                📈 Máximo: {{ $inv->stock_maximo }} | 
                🔔 Reorden: {{ $inv->punto_reorden }}
            </span>
        </div>
    @empty
        <p class="text-gray-500 text-center">No hay configuración de stock para este producto.</p>
    @endforelse
</div>