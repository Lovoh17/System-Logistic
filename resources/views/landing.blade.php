<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgroAlvarado — Sistema de Gestión Logística</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="shortcut icon" href="{{ asset('images/logo.png') }}" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --ink:        #0f1910;
            --ink-soft:   #3a4a3c;
            --ink-muted:  #6b7a6d;
            --leaf:       #1c4a1c;
            --leaf-mid:   #2d6a2d;
            --leaf-light: #e8f0e8;
            --blue:       #1a3358;
            --blue-light: #e8eef6;
            --amber:      #7c4a00;
            --amber-light:#f5ede0;
            --rule:       #dde5dd;
            --bg:         #f8faf8;
            --white:      #ffffff;
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'DM Sans', sans-serif;
            font-weight: 400;
            background: var(--bg);
            color: var(--ink);
            -webkit-font-smoothing: antialiased;
        }

        /* ── HEADER ──────────────────────────────────── */
        header {
            background: var(--white);
            border-bottom: 1px solid var(--rule);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-inner {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: .75rem;
            text-decoration: none;
        }

        .brand-mark {
            width: 36px; height: 36px;
            background: var(--ink);
            border-radius: 6px;
            display: flex; align-items: center; justify-content: center;
        }

        .brand-mark svg { width: 20px; height: 20px; fill: #fff; }

        .brand-name {
            font-family: 'DM Serif Display', serif;
            font-size: 1.15rem;
            color: var(--ink);
            letter-spacing: .01em;
        }

        .brand-name span { color: var(--leaf-mid); }

        nav { display: flex; gap: 1.5rem; align-items: center; }

        nav a {
            font-size: .82rem;
            font-weight: 500;
            color: var(--ink-muted);
            text-decoration: none;
            letter-spacing: .03em;
            text-transform: uppercase;
            transition: color .15s;
        }

        nav a:hover { color: var(--ink); }

        /* ── HERO ─────────────────────────────────────── */
        .hero {
            max-width: 1200px;
            margin: 0 auto;
            padding: 6rem 2rem 5rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
        }

        .hero-label {
            display: inline-block;
            font-size: .7rem;
            font-weight: 600;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: var(--leaf-mid);
            background: var(--leaf-light);
            border: 1px solid #c8dcc8;
            padding: .3rem .85rem;
            border-radius: 2px;
            margin-bottom: 1.5rem;
        }

        .hero h1 {
            font-family: 'DM Serif Display', serif;
            font-size: 3.6rem;
            line-height: 1.08;
            color: var(--ink);
            letter-spacing: -.02em;
            margin-bottom: 1.25rem;
        }

        .hero h1 em {
            font-style: italic;
            color: var(--leaf-mid);
        }

        .hero-desc {
            font-size: 1rem;
            line-height: 1.7;
            color: var(--ink-soft);
            max-width: 420px;
            margin-bottom: 2rem;
        }

        .hero-cta {
            display: inline-flex;
            align-items: center;
            gap: .6rem;
            background: var(--ink);
            color: #fff;
            text-decoration: none;
            font-size: .82rem;
            font-weight: 600;
            letter-spacing: .05em;
            text-transform: uppercase;
            padding: .8rem 1.6rem;
            border-radius: 3px;
            transition: background .2s;
        }

        .hero-cta:hover { background: var(--leaf); }
        .hero-cta svg { width: 14px; height: 14px; stroke: currentColor; fill: none; }

        /* Hero visual */
        .hero-visual {
            position: relative;
        }

        .hero-grid-panel {
            background: var(--white);
            border: 1px solid var(--rule);
            border-radius: 4px;
            overflow: hidden;
        }

        .hgp-header {
            background: var(--ink);
            padding: .65rem 1rem;
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .hgp-dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            background: rgba(255,255,255,.2);
        }

        .hgp-title {
            font-size: .7rem;
            color: rgba(255,255,255,.6);
            letter-spacing: .08em;
            text-transform: uppercase;
            font-weight: 500;
            margin-left: .25rem;
        }

        .hgp-body { padding: 1rem; }

        .hgp-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: .55rem .75rem;
            border-radius: 3px;
            margin-bottom: .35rem;
            font-size: .78rem;
        }

        .hgp-row:last-child { margin-bottom: 0; }
        .hgp-row:nth-child(odd) { background: var(--bg); }

        .hgp-row-label { color: var(--ink-soft); font-weight: 500; }

        .badge {
            font-size: .68rem;
            font-weight: 600;
            padding: .2rem .6rem;
            border-radius: 2px;
            letter-spacing: .04em;
        }

        .badge-green  { background: var(--leaf-light); color: var(--leaf); }
        .badge-amber  { background: var(--amber-light); color: var(--amber); }
        .badge-blue   { background: var(--blue-light); color: var(--blue); }
        .badge-muted  { background: #eee; color: var(--ink-muted); }

        .hero-stat-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: .75rem;
            margin-top: .75rem;
        }

        .hero-stat {
            background: var(--white);
            border: 1px solid var(--rule);
            border-radius: 3px;
            padding: .85rem 1rem;
        }

        .hero-stat-num {
            font-family: 'DM Serif Display', serif;
            font-size: 1.6rem;
            color: var(--ink);
            line-height: 1;
        }

        .hero-stat-label {
            font-size: .68rem;
            color: var(--ink-muted);
            text-transform: uppercase;
            letter-spacing: .07em;
            margin-top: .3rem;
        }

        /* ── DIVIDER ─────────────────────────────────── */
        .divider {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            border-top: 1px solid var(--rule);
        }

        /* ── PANELS ──────────────────────────────────── */
        .panels-section {
            max-width: 1200px;
            margin: 0 auto;
            padding: 5rem 2rem;
        }

        .section-label {
            font-size: .7rem;
            font-weight: 600;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: var(--ink-muted);
            margin-bottom: 1rem;
        }

        .section-title {
            font-family: 'DM Serif Display', serif;
            font-size: 2.4rem;
            color: var(--ink);
            margin-bottom: .75rem;
            letter-spacing: -.02em;
        }

        .section-desc {
            font-size: .95rem;
            color: var(--ink-soft);
            line-height: 1.65;
            max-width: 520px;
            margin-bottom: 3rem;
        }

        .panels-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.25rem;
        }

        .panel-card {
            background: var(--white);
            border: 1px solid var(--rule);
            border-radius: 4px;
            overflow: hidden;
            transition: box-shadow .2s, transform .2s;
        }

        .panel-card:hover {
            box-shadow: 0 12px 32px rgba(0,0,0,.09);
            transform: translateY(-4px);
        }

        .panel-card-accent {
            height: 3px;
        }

        .accent-green  { background: var(--leaf-mid); }
        .accent-blue   { background: var(--blue); }
        .accent-amber  { background: var(--amber); }

        .panel-card-body { padding: 1.75rem; }

        .panel-icon {
            width: 44px; height: 44px;
            border-radius: 6px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 1.25rem;
        }

        .icon-green  { background: var(--leaf-light); }
        .icon-blue   { background: var(--blue-light); }
        .icon-amber  { background: var(--amber-light); }

        .panel-icon svg {
            width: 22px; height: 22px;
            stroke-width: 1.75;
            fill: none;
            stroke: currentColor;
        }

        .icon-green svg  { color: var(--leaf); }
        .icon-blue svg   { color: var(--blue); }
        .icon-amber svg  { color: var(--amber); }

        .panel-title {
            font-family: 'DM Serif Display', serif;
            font-size: 1.25rem;
            color: var(--ink);
            margin-bottom: .35rem;
        }

        .panel-desc {
            font-size: .82rem;
            color: var(--ink-muted);
            line-height: 1.6;
            margin-bottom: 1.25rem;
        }

        .panel-features {
            list-style: none;
            margin-bottom: 1.75rem;
        }

        .panel-features li {
            font-size: .78rem;
            color: var(--ink-soft);
            padding: .35rem 0;
            border-bottom: 1px solid var(--rule);
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .panel-features li:last-child { border-bottom: none; }

        .feat-dot {
            width: 5px; height: 5px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .dot-green { background: var(--leaf-mid); }
        .dot-blue  { background: var(--blue); }
        .dot-amber { background: var(--amber); }

        .panel-btn {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            padding: .72rem 1rem;
            border-radius: 3px;
            font-size: .78rem;
            font-weight: 600;
            letter-spacing: .04em;
            text-transform: uppercase;
            text-decoration: none;
            transition: opacity .15s;
        }

        .panel-btn:hover { opacity: .88; }

        .btn-green { background: var(--leaf); color: #fff; }
        .btn-blue  { background: var(--blue); color: #fff; }
        .btn-amber { background: var(--amber); color: #fff; }

        .panel-btn svg {
            width: 14px; height: 14px;
            stroke: currentColor; fill: none;
            stroke-width: 2;
        }

        /* ── STRIP ───────────────────────────────────── */
        .strip {
            background: var(--ink);
            padding: 3.5rem 2rem;
        }

        .strip-inner {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1px;
            background: rgba(255,255,255,.1);
        }

        .strip-item {
            background: var(--ink);
            padding: 2rem 2.5rem;
        }

        .strip-num {
            font-family: 'DM Serif Display', serif;
            font-size: 2.8rem;
            color: #fff;
            line-height: 1;
            margin-bottom: .4rem;
        }

        .strip-label {
            font-size: .78rem;
            color: rgba(255,255,255,.5);
            text-transform: uppercase;
            letter-spacing: .08em;
            font-weight: 500;
        }

        /* ── FOOTER ──────────────────────────────────── */
        footer {
            background: var(--white);
            border-top: 1px solid var(--rule);
            padding: 2rem;
        }

        .footer-inner {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .footer-copy {
            font-size: .78rem;
            color: var(--ink-muted);
        }

        .footer-links { display: flex; gap: 1.5rem; }

        .footer-links a {
            font-size: .78rem;
            color: var(--ink-muted);
            text-decoration: none;
            transition: color .15s;
        }

        .footer-links a:hover { color: var(--ink); }

        /* ── ANIMATIONS ──────────────────────────────── */
        .fade-up {
            opacity: 0;
            transform: translateY(20px);
            animation: fadeUp .6s ease forwards;
        }

        @keyframes fadeUp {
            to { opacity: 1; transform: translateY(0); }
        }

        .delay-1 { animation-delay: .1s; }
        .delay-2 { animation-delay: .2s; }
        .delay-3 { animation-delay: .3s; }
        .delay-4 { animation-delay: .4s; }
        .delay-5 { animation-delay: .5s; }

        /* ── RESPONSIVE ──────────────────────────────── */
        @media (max-width: 900px) {
            .hero { grid-template-columns: 1fr; padding: 3.5rem 1.5rem 3rem; gap: 2.5rem; }
            .hero h1 { font-size: 2.6rem; }
            .panels-grid { grid-template-columns: 1fr; }
            .strip-inner { grid-template-columns: 1fr; }
            .strip-item { padding: 1.5rem 2rem; }
            nav { display: none; }
        }
    </style>
</head>
<body>

    <!-- Header -->
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