<div class="p-4" style="font-family: 'Courier New', monospace;">
    <div class="text-center mb-4">
        <h2 class="text-xl font-bold">AgroAlvarado</h2>
        <p class="text-sm">Agricultura y Ferretería</p>
        <p class="text-xs">NIT: 0614-150390-101-5</p>
        <p class="text-xs">Tel: (503) 2222-3333</p>
        <hr class="my-2 border-dashed">
        <p class="font-bold">TICKET DE VENTA</p>
        <p class="text-xs">N°: {{ $pedido->numero }}</p>
        <p class="text-xs">Fecha: {{ $pedido->fecha_pedido->format('d/m/Y H:i') }}</p>
        <hr class="my-2 border-dashed">
    </div>
    
    <div class="mb-3">
        <p class="font-semibold">Cliente:</p>
        <p class="text-sm">{{ $pedido->cliente->nombre }}</p>
    </div>
    
    <hr class="my-2 border-dashed">
    
    <div class="mb-3">
        <table class="w-full text-xs">
            <thead>
                <tr class="border-b">
                    <th class="text-left">Producto</th>
                    <th class="text-center">Cant</th>
                    <th class="text-right">Precio</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pedido->items as $item)
                <tr>
                    <td class="text-left">{{ substr($item->producto->nombre, 0, 20) }}</td>
                    <td class="text-center">{{ $item->cantidad }}</td>
                    <td class="text-right">${{ number_format($item->precio_unitario, 2) }}</td>
                    <td class="text-right">${{ number_format($item->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <hr class="my-2 border-dashed">
    
    <div class="mb-2">
        <div class="flex justify-between text-sm">
            <span>Subtotal:</span>
            <span>${{ number_format($pedido->subtotal, 2) }}</span>
        </div>
        <div class="flex justify-between text-sm">
            <span>IVA (13%):</span>
            <span>${{ number_format($pedido->impuesto, 2) }}</span>
        </div>
        <div class="flex justify-between text-lg font-bold mt-2 pt-2 border-t">
            <span>TOTAL:</span>
            <span>${{ number_format($pedido->total, 2) }}</span>
        </div>
    </div>
    
    <hr class="my-2 border-dashed">
    
    <div class="text-center text-xs">
        <p>¡Gracias por su compra!</p>
        <p>Atendido por: {{ auth()->user()->name }}</p>
        <p class="mt-2">Por favor conservar este ticket</p>
    </div>
</div>