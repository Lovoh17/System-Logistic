<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgroAlvarado - Sistema de Gestión Logística</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e9edf2 100%);
        }
        .bg-gradient-primary {
            background: radial-gradient(circle at 20% 30%, #1a472a, #0a1f10);
            position: relative;
            overflow: hidden;
        }
        .bg-gradient-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100' opacity='0.05'%3E%3Cpath fill='white' d='M10,10 L30,10 L40,30 L30,50 L10,50 L0,30 Z'/%3E%3Cpath fill='white' d='M60,10 L80,10 L90,30 L80,50 L60,50 L50,30 Z'/%3E%3Cpath fill='white' d='M35,60 L55,60 L65,80 L55,95 L35,95 L25,80 Z'/%3E%3C/svg%3E") repeat;
            pointer-events: none;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .glass-card:hover {
            transform: translateY(-12px);
            box-shadow: 0 30px 45px -20px rgba(0,0,0,0.3);
        }
        .btn-admin {
            background: linear-gradient(95deg, #1a472a 0%, #2d6a2d 100%);
            position: relative;
            overflow: hidden;
        }
        .btn-admin:hover {
            background: linear-gradient(95deg, #2d6a2d 0%, #3d8b3d 100%);
            transform: translateY(-2px);
        }
        .btn-ventas {
            background: linear-gradient(95deg, #1e3a5f 0%, #2563eb 100%);
            position: relative;
            overflow: hidden;
        }
        .btn-ventas:hover {
            background: linear-gradient(95deg, #2563eb 0%, #3b82f6 100%);
            transform: translateY(-2px);
        }
        .btn-ventas::after, .btn-admin::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -60%;
            width: 200%;
            height: 200%;
            background: rgba(255,255,255,0.1);
            transform: rotate(30deg);
            transition: all 0.5s;
        }
        .btn-ventas:hover::after, .btn-admin:hover::after {
            left: 100%;
        }
        .stat-card {
            background: white;
            border-radius: 24px;
            transition: all 0.3s;
        }
        .stat-card:hover {
            transform: scale(1.02);
        }
        .pulse {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(45,106,45,0.4); }
            70% { box-shadow: 0 0 0 15px rgba(45,106,45,0); }
            100% { box-shadow: 0 0 0 0 rgba(45,106,45,0); }
        }
        .feature-badge {
            background: linear-gradient(135deg, rgba(45,106,45,0.1), rgba(37,99,235,0.1));
        }
    </style>
</head>
<body class="antialiased">

    <!-- Hero Section con efecto parallax -->
    <div class="bg-gradient-primary text-white relative">
        <div class="container mx-auto px-4 py-20 md:py-28 relative z-10">
            <div class="text-center max-w-4xl mx-auto">
                <div class="mb-6 animate__animated animate__fadeInDown">
                    <img src="{{ asset('images/logo.png') }}" alt="AgroAlvarado" class="h-28 mx-auto bg-white/10 p-3 rounded-3xl backdrop-blur-sm pulse" onerror="this.src='https://placehold.co/120x120/2d6a2d/white?text=🌾'">
                </div>
                <h1 class="text-5xl md:text-7xl font-black mb-4 tracking-tight animate__animated animate__fadeInUp">
                    Agro<span class="text-[#4caf50]">Alvarado</span>
                </h1>
                <p class="text-xl md:text-2xl mb-6 opacity-95 font-light animate__animated animate__fadeInUp animate__delay-1s">
                    Sistema de Gestión Logística y Punto de Venta
                </p>
                <p class="text-lg max-w-2xl mx-auto opacity-80 animate__animated animate__fadeInUp animate__delay-2s">
                    La solución integral para controlar inventarios, pedidos, envíos y ventas en tiempo real.
                </p>
            </div>
        </div>
        <!-- Ola decorativa -->
        <div class="absolute bottom-0 w-full">
            <svg viewBox="0 0 1200 120" preserveAspectRatio="none" class="relative block w-full h-12">
                <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" fill="#f5f7fa"></path>
            </svg>
        </div>
    </div>

    <!-- Stats Section con gradiente sutil -->
    <div class="py-16 bg-gradient-to-b from-[#f5f7fa] to-white">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="stat-card p-8 text-center shadow-lg">
                    <div class="text-5xl font-black text-green-700 mb-3">99%</div>
                    <div class="text-gray-600 font-medium">Eficiencia en entregas</div>
                    <div class="text-sm text-gray-400 mt-2">Tasa de éxito en despachos</div>
                </div>
                <div class="stat-card p-8 text-center shadow-lg transform md:-translate-y-4">
                    <div class="text-5xl font-black text-green-700 mb-3">24/7</div>
                    <div class="text-gray-600 font-medium">Trazabilidad en tiempo real</div>
                    <div class="text-sm text-gray-400 mt-2">Monitoreo continuo</div>
                </div>
                <div class="stat-card p-8 text-center shadow-lg">
                    <div class="text-5xl font-black text-green-700 mb-3">+500</div>
                    <div class="text-gray-600 font-medium">Empresas confían</div>
                    <div class="text-sm text-gray-400 mt-2">Clientes satisfechos</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cards Section con diseño moderno -->
    <div class="container mx-auto px-4 py-20">
        <div class="text-center mb-16">
            <span class="text-green-700 font-semibold text-sm uppercase tracking-wider bg-green-100 px-4 py-1 rounded-full">Acceso al sistema</span>
            <h2 class="text-4xl md:text-5xl font-bold text-gray-800 mt-4 mb-4">Selecciona tu <span class="text-green-700">área de trabajo</span></h2>
            <p class="text-gray-500 max-w-2xl mx-auto text-lg">Elige el panel que necesitas según tu rol y comienza a trabajar</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-10 max-w-5xl mx-auto">
            <!-- Panel Administrativo -->
            <div class="glass-card rounded-3xl overflow-hidden shadow-xl group">
                <div class="h-2 bg-gradient-to-r from-green-700 to-green-500"></div>
                <div class="p-8">
                    <div class="w-20 h-20 bg-gradient-to-br from-green-100 to-green-50 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-10 h-10 text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-center text-gray-800 mb-2">Administración</h3>
                    <p class="text-gray-500 text-center mb-6">Control total del negocio</p>
                    <div class="flex flex-wrap justify-center gap-2 mb-6">
                        <span class="feature-badge text-green-800 text-sm px-3 py-1 rounded-full">Inventario</span>
                        <span class="feature-badge text-green-800 text-sm px-3 py-1 rounded-full">Proveedores</span>
                        <span class="feature-badge text-green-800 text-sm px-3 py-1 rounded-full">Clientes</span>
                        <span class="feature-badge text-green-800 text-sm px-3 py-1 rounded-full">Envíos</span>
                        <span class="feature-badge text-green-800 text-sm px-3 py-1 rounded-full">Reportes</span>
                    </div>
                    <a href="/admin/login" class="btn-admin text-white font-semibold py-4 px-6 rounded-xl shadow-lg flex items-center justify-center gap-2 transition-all">
                        <span>Acceder a Administración</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Panel de Ventas -->
            <div class="glass-card rounded-3xl overflow-hidden shadow-xl group">
                <div class="h-2 bg-gradient-to-r from-blue-700 to-blue-500"></div>
                <div class="p-8">
                    <div class="w-20 h-20 bg-gradient-to-br from-blue-100 to-blue-50 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-10 h-10 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-1 6M18 13l1 6M9 21h6M12 17v4"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-center text-gray-800 mb-2">Punto de Venta</h3>
                    <p class="text-gray-500 text-center mb-6">Ventas rápidas y eficientes</p>
                    <div class="flex flex-wrap justify-center gap-2 mb-6">
                        <span class="feature-badge text-blue-800 text-sm px-3 py-1 rounded-full">Ventas rápidas</span>
                        <span class="feature-badge text-blue-800 text-sm px-3 py-1 rounded-full">Clientes</span>
                        <span class="feature-badge text-blue-800 text-sm px-3 py-1 rounded-full">Pedidos</span>
                        <span class="feature-badge text-blue-800 text-sm px-3 py-1 rounded-full">Pagos</span>
                        <span class="feature-badge text-blue-800 text-sm px-3 py-1 rounded-full">Facturación</span>
                    </div>
                    <a href="/ventas/login" class="btn-ventas text-white font-semibold py-4 px-6 rounded-xl shadow-lg flex items-center justify-center gap-2 transition-all">
                        <span>Acceder a Punto de Venta</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>



    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-400 py-10">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-4 md:mb-0">
                    <span class="text-xl font-bold text-white">AgroAlvarado</span>
                    <p class="text-sm mt-1">Sistema de Gestión Logística</p>
                </div>
                <div class="flex gap-6">
                    <a href="#" class="hover:text-white transition">Términos</a>
                    <a href="#" class="hover:text-white transition">Privacidad</a>
                    <a href="#" class="hover:text-white transition">Soporte</a>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-6 pt-6 text-center text-sm">
                © 2026 AgroAlvarado - Todos los derechos reservados
            </div>
        </div>
    </footer>

</body>
</html>