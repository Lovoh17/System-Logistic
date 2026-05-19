<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgroAlvarado — Sistema de Gestión Logística</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="{{ asset('css/filament/filament/style.css') }}">
    <link rel="shortcut icon" href="{{ asset('images/logo.png') }}" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <div class="header-inner">
            <a href="/" class="brand">
                <span class="brand-name">Agro<span>Alvarado</span></span>
            </a>
            <nav>
                <a href="#paneles">Acceso</a>
                <a href="#">Soporte</a>
                <a href="#">Términos</a>
            </nav>
        </div>
    </header>

    <!-- Hero -->
    <section>
        <div class="hero">
            <div>
                <span class="hero-label fade-up">Sistema Logístico Integral</span>
                <h1 class="fade-up delay-1">Gestión <em>precisa</em> de tu cadena de suministro</h1>
                <p class="hero-desc fade-up delay-2">
                    Controla inventario, pedidos, envíos y ventas desde una plataforma unificada.
                    Diseñado para distribuidoras y empresas comerciales en El Salvador.
                </p>
                <a href="#paneles" class="hero-cta fade-up delay-3">
                    Seleccionar panel
                    <svg viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </a>
            </div>

            <div class="hero-visual fade-up delay-2">
                <div class="hero-grid-panel">
                    <div class="hgp-header">
                        <div class="hgp-dot"></div>
                        <div class="hgp-dot"></div>
                        <div class="hgp-dot"></div>
                        <span class="hgp-title">Estado del sistema</span>
                    </div>
                    <div class="hgp-body">
                        <div class="hgp-row">
                            <span class="hgp-row-label">Pedidos activos</span>
                            <span class="badge badge-blue">En proceso</span>
                        </div>
                        <div class="hgp-row">
                            <span class="hgp-row-label">Envíos en ruta</span>
                            <span class="badge badge-amber">En tránsito</span>
                        </div>
                        <div class="hgp-row">
                            <span class="hgp-row-label">Stock crítico</span>
                            <span class="badge badge-green">Controlado</span>
                        </div>
                        <div class="hgp-row">
                            <span class="hgp-row-label">Transportistas</span>
                            <span class="badge badge-green">Disponibles</span>
                        </div>
                        <div class="hgp-row">
                            <span class="hgp-row-label">Entregas hoy</span>
                            <span class="badge badge-green">Completadas</span>
                        </div>
                    </div>
                </div>
                <div class="hero-stat-row">
                    <div class="hero-stat">
                        <div class="hero-stat-num">99%</div>
                        <div class="hero-stat-label">Entregas</div>
                    </div>
                    <div class="hero-stat">
                        <div class="hero-stat-num">24/7</div>
                        <div class="hero-stat-label">Monitoreo</div>
                    </div>
                    <div class="hero-stat">
                        <div class="hero-stat-num">+500</div>
                        <div class="hero-stat-label">Empresas</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="divider"></div>

    <!-- Panels -->
    <section class="panels-section" id="paneles">
        <p class="section-label">Acceso al sistema</p>
        <h2 class="section-title">Selecciona tu área de trabajo</h2>
        <p class="section-desc">
            Cada panel está diseñado para un rol específico. Accede con tus credenciales
            y trabaja desde el entorno que corresponde a tus responsabilidades.
        </p>

        <div class="panels-grid">

            <!-- Administración -->
            <div class="panel-card fade-up delay-1">
                <div class="panel-card-accent accent-green"></div>
                <div class="panel-card-body">
                    <div class="panel-icon icon-green">
                        <svg viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <h3 class="panel-title">Administración</h3>
                    <p class="panel-desc">Control total del negocio. Gestión de recursos, reportes y configuración del sistema.</p>
                    <ul class="panel-features">
                        <li><span class="feat-dot dot-green"></span>Gestión de inventario y stock</li>
                        <li><span class="feat-dot dot-green"></span>Proveedores y compras</li>
                        <li><span class="feat-dot dot-green"></span>Clientes y pedidos de venta</li>
                        <li><span class="feat-dot dot-green"></span>Reportes y trazabilidad</li>
                        <li><span class="feat-dot dot-green"></span>Configuración del sistema</li>
                    </ul>
                    <a href="/admin/login" class="panel-btn btn-green">
                        Acceder a Administración
                        <svg viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Punto de Venta -->
            <div class="panel-card fade-up delay-2">
                <div class="panel-card-accent accent-blue"></div>
                <div class="panel-card-body">
                    <div class="panel-icon icon-blue">
                        <svg viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="panel-title">Punto de Venta</h3>
                    <p class="panel-desc">Interfaz de caja optimizada para velocidad. Procesa ventas y gestiona clientes con agilidad.</p>
                    <ul class="panel-features">
                        <li><span class="feat-dot dot-blue"></span>Ventas directas en caja</li>
                        <li><span class="feat-dot dot-blue"></span>Búsqueda rápida de productos</li>
                        <li><span class="feat-dot dot-blue"></span>Gestión de clientes</li>
                        <li><span class="feat-dot dot-blue"></span>Historial de pedidos</li>
                        <li><span class="feat-dot dot-blue"></span>Control de stock por sucursal</li>
                    </ul>
                    <a href="/ventas/login" class="panel-btn btn-blue">
                        Acceder a Punto de Venta
                        <svg viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Logística -->
            <div class="panel-card fade-up delay-3">
                <div class="panel-card-accent accent-amber"></div>
                <div class="panel-card-body">
                    <div class="panel-icon icon-amber">
                        <svg viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="panel-title">Logística</h3>
                    <p class="panel-desc">Supervisión de operaciones de transporte y entrega. Seguimiento en tiempo real de la flota.</p>
                    <ul class="panel-features">
                        <li><span class="feat-dot dot-amber"></span>Mapa de transportistas GPS</li>
                        <li><span class="feat-dot dot-amber"></span>Control de envíos y entregas</li>
                        <li><span class="feat-dot dot-amber"></span>Trazabilidad de pedidos</li>
                        <li><span class="feat-dot dot-amber"></span>Gestión de flota vehicular</li>
                        <li><span class="feat-dot dot-amber"></span>Registro de seguimiento</li>
                    </ul>
                    <a href="/logistica/login" class="panel-btn btn-amber">
                        Acceder a Logística
                        <svg viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </a>
                </div>
            </div>

        </div>
    </section>

    <!-- Strip de métricas -->
    <div class="strip">
        <div class="strip-inner">
            <div class="strip-item">
                <div class="strip-num">99%</div>
                <div class="strip-label">Eficiencia en entregas</div>
            </div>
            <div class="strip-item">
                <div class="strip-num">24/7</div>
                <div class="strip-label">Trazabilidad en tiempo real</div>
            </div>
            <div class="strip-item">
                <div class="strip-num">+500</div>
                <div class="strip-label">Empresas confían en el sistema</div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="footer-inner">
            <span class="footer-copy">© 2026 AgroAlvarado — Todos los derechos reservados</span>
            <div class="footer-links">
                <a href="#">Términos</a>
                <a href="#">Privacidad</a>
                <a href="#">Soporte</a>
            </div>
        </div>
    </footer>

</body>
</html>