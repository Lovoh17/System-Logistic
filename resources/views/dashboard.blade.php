<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TraceLog - Punto de Venta</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <nav class="bg-green-700 text-white p-4">
            <div class="container mx-auto flex justify-between">
                <h1 class="text-xl font-bold">🏪 Punto de Venta - AgroAlvarado</h1>
                <div>
                    <span>{{ auth()->user()->name }}</span>
                    <form method="POST" action="/logout" class="inline ml-4">
                        @csrf
                        <button type="submit" class="bg-red-500 px-3 py-1 rounded">Salir</button>
                    </form>
                </div>
            </div>
        </nav>
        
        <div class="container mx-auto p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-bold mb-4">🛒 Nuevo Pedido</h2>
                    <a href="/admin/pedido-ventas/create" class="bg-green-600 text-white px-4 py-2 rounded inline-block">Crear Pedido</a>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-bold mb-4">👥 Clientes</h2>
                    <a href="/admin/clientes" class="bg-blue-600 text-white px-4 py-2 rounded inline-block">Ver Clientes</a>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-bold mb-4">📋 Mis Pedidos</h2>
                    <a href="/admin/pedido-ventas" class="bg-purple-600 text-white px-4 py-2 rounded inline-block">Ver Pedidos</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>