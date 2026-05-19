{{--
    ╔══════════════════════════════════════════════════════╗
    ║  TraceLog — Login Page                              ║
    ║  IMPORTANTE: Los estilos usan el selector           ║
    ║  body.tl-login-page para NO contaminar el resto     ║
    ║  del panel (modales, tablas, etc.)                  ║
    ╚══════════════════════════════════════════════════════╝
--}}

{{-- Añadir clase al body SOLO en esta página --}}
@push('styles')
<style>
    /* ══════════════════════════════════════════════════════
       TODOS los estilos van bajo body.tl-login-page
       para que NO afecten ninguna otra página del panel
    ══════════════════════════════════════════════════════ */

    body.tl-login-page {
        margin: 0 !important;
        padding: 0 !important;
        overflow: hidden;
        background: #0d2b0d !important;
    }

    /* Variables — solo disponibles dentro del login */
    body.tl-login-page {
        --tl-green-dark:   #1a4a1a;
        --tl-green-mid:    #2d6a2d;
        --tl-green-accent: #3d8b3d;
        --tl-warm-white:   #fefefe;
        --tl-text-dark:    #1a2e1a;
        --tl-text-muted:   #6b7c6b;
        --tl-border:       #d8e5d8;
        --tl-input-bg:     #f4f8f4;
    }

    body.tl-login-page .tl-login-wrap {
        display: flex;
        width: 100vw;
        height: 100vh;
        font-family: Georgia, 'Times New Roman', serif;
        position: fixed;
        inset: 0;
        z-index: 1;               /* Por encima del body pero NO sobre modales */
    }

    /* ── PANEL IZQUIERDO ─────────────────────────────── */
    body.tl-login-page .tl-hero {
        position: relative;
        width: 45%;
        height: 100vh;
        overflow: hidden;
        flex-shrink: 0;
    }

    body.tl-login-page .tl-hero-fallback {
        position: absolute;
        inset: 0;
        background: linear-gradient(160deg, #0d2b0d 0%, #1a4a1a 40%, #2d6a2d 100%);
    }

    body.tl-login-page .tl-hero-bg {
        position: absolute;
        inset: 0;
        background:
            linear-gradient(to bottom,
                rgba(10,30,10,.35) 0%,
                rgba(10,30,10,.15) 40%,
                rgba(5,18,5,.78) 100%),
            url('https://images.unsplash.com/photo-1586771107445-d3ca888129ff?w=900&q=80&fit=crop')
            center/cover no-repeat;
    }

    body.tl-login-page .tl-hero-grid {
        position: absolute;
        inset: 0;
        background-image:
            linear-gradient(rgba(77,175,80,.05) 1px, transparent 1px),
            linear-gradient(90deg, rgba(77,175,80,.05) 1px, transparent 1px);
        background-size: 40px 40px;
        z-index: 1;
    }

    body.tl-login-page .tl-logo {
        position: absolute;
        top: 1.8rem; left: 2rem;
        z-index: 10;
        display: flex; align-items: center; gap: .6rem;
    }

    body.tl-login-page .tl-logo-icon {
        width: 34px; height: 34px;
        background: rgba(77,175,80,.9);
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
    }

    body.tl-login-page .tl-logo-icon svg { width: 20px; height: 20px; fill: #fff; }

    body.tl-login-page .tl-logo-name {
        color: #fff; font-size: 1.05rem; font-weight: 700;
        text-shadow: 0 1px 4px rgba(0,0,0,.45);
    }

    body.tl-login-page .tl-hero-bottom {
        position: absolute;
        bottom: 2.5rem; left: 2rem; right: 2rem;
        z-index: 10;
    }

    body.tl-login-page .tl-stats { display: flex; gap: .65rem; margin-bottom: 1.3rem; }

    body.tl-login-page .tl-stat {
        background: rgba(255,255,255,.12);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,.2);
        border-radius: 8px;
        padding: .45rem .75rem;
        text-align: center;
    }

    body.tl-login-page .tl-stat strong { display: block; color: #fff; font-size: 1rem; font-weight: 700; }
    body.tl-login-page .tl-stat span {
        display: block; color: rgba(255,255,255,.62);
        font-size: .62rem; font-family: Arial, sans-serif;
        text-transform: uppercase; letter-spacing: .07em; margin-top: 1px;
    }

    body.tl-login-page .tl-tagline h2 {
        color: #fff; font-size: 2rem; font-weight: 700;
        line-height: 1.2; margin: 0 0 .7rem;
        text-shadow: 0 2px 12px rgba(0,0,0,.55);
    }

    body.tl-login-page .tl-tagline p {
        color: rgba(255,255,255,.72); font-size: .8rem;
        line-height: 1.55; font-family: Arial, sans-serif;
        margin: 0; text-shadow: 0 1px 6px rgba(0,0,0,.5);
    }

    /* ── PANEL DERECHO ───────────────────────────────── */
    body.tl-login-page .tl-form-side {
        flex: 1;
        display: flex; align-items: center; justify-content: center;
        background: #fefefe;
        padding: 2.5rem 3rem;
        overflow-y: auto;
        height: 100vh;
    }

    body.tl-login-page .tl-form-box { width: 100%; max-width: 360px; }

    body.tl-login-page .tl-form-box h1 {
        font-family: Georgia, serif;
        font-size: 1.8rem; font-weight: 700;
        color: #1a2e1a; margin: 0 0 .35rem;
    }

    body.tl-login-page .tl-sub {
        font-family: Arial, sans-serif; font-size: .845rem;
        color: #6b7c6b; margin: 0 0 1.75rem;
    }

    body.tl-login-page .tl-form-box label {
        font-family: Arial, sans-serif !important;
        font-size: .7rem !important; font-weight: 700 !important;
        color: #1a2e1a !important; text-transform: uppercase !important;
        letter-spacing: .07em !important;
    }

    body.tl-login-page .tl-form-box input[type="email"],
    body.tl-login-page .tl-form-box input[type="password"],
    body.tl-login-page .tl-form-box input[type="text"] {
        font-family: Arial, sans-serif !important;
        font-size: .9rem !important;
        background: #f4f8f4 !important;
        border: 1.5px solid #d8e5d8 !important;
        border-radius: 8px !important;
        padding: .65rem .9rem !important;
        color: #1a2e1a !important;
        width: 100% !important;
        box-sizing: border-box !important;
        transition: border-color .2s, box-shadow .2s !important;
    }

    body.tl-login-page .tl-form-box input[type="email"]:focus,
    body.tl-login-page .tl-form-box input[type="password"]:focus,
    body.tl-login-page .tl-form-box input[type="text"]:focus {
        outline: none !important;
        border-color: #3d8b3d !important;
        box-shadow: 0 0 0 3px rgba(77,175,80,.12) !important;
        background: #fff !important;
    }

    body.tl-login-page .tl-form-box input[type="checkbox"] {
        accent-color: #3d8b3d !important;
    }

    body.tl-login-page .tl-form-box a {
        color: #2d6a2d !important;
        font-family: Arial, sans-serif !important;
        font-size: .82rem !important;
    }

    body.tl-login-page .tl-form-box button[type="submit"] {
        background: #1a4a1a !important;
        border: none !important; border-radius: 8px !important;
        color: #fff !important;
        font-family: Arial, sans-serif !important;
        font-size: .88rem !important; font-weight: 600 !important;
        letter-spacing: .04em !important;
        padding: .75rem 1.5rem !important;
        width: 100% !important; cursor: pointer !important;
        transition: background .2s, transform .1s !important;
    }

    body.tl-login-page .tl-form-box button[type="submit"]:hover {
        background: #2d6a2d !important;
        transform: translateY(-1px) !important;
    }

    body.tl-login-page .tl-sep {
        display: flex; align-items: center; gap: .7rem;
        margin: 1.4rem 0;
    }

    body.tl-login-page .tl-sep hr {
        flex: 1; border: none; border-top: 1px solid #d8e5d8; margin: 0;
    }

    body.tl-login-page .tl-sep span {
        font-family: Arial, sans-serif; font-size: .68rem;
        color: #6b7c6b; text-transform: uppercase;
        letter-spacing: .1em; white-space: nowrap;
    }

    body.tl-login-page .tl-oauth {
        display: grid; grid-template-columns: 1fr 1fr;
        gap: .6rem; margin-bottom: 1.5rem;
    }

    body.tl-login-page .tl-oauth-btn {
        display: flex; align-items: center; justify-content: center;
        gap: .45rem; padding: .58rem .8rem;
        border: 1.5px solid #d8e5d8; border-radius: 8px;
        background: #fff; color: #1a2e1a !important;
        font-family: Arial, sans-serif !important;
        font-size: .8rem !important; font-weight: 500;
        text-decoration: none !important; cursor: pointer;
        transition: border-color .18s, background .18s;
    }

    body.tl-login-page .tl-oauth-btn:hover {
        border-color: #3d8b3d !important;
        background: #f4f8f4 !important;
    }

    body.tl-login-page .tl-oauth-btn svg { width: 15px; height: 15px; flex-shrink: 0; }

    body.tl-login-page .tl-reg {
        text-align: center; font-family: Arial, sans-serif;
        font-size: .81rem; color: #6b7c6b; margin-bottom: 1.2rem;
    }

    body.tl-login-page .tl-reg a {
        color: #1a4a1a !important; font-weight: 600 !important;
        text-decoration: none !important;
    }

    body.tl-login-page .tl-footer {
        text-align: center; padding-top: 1.1rem;
        border-top: 1px solid #d8e5d8;
    }

    body.tl-login-page .tl-footer a {
        font-family: Arial, sans-serif !important; font-size: .72rem !important;
        color: #6b7c6b !important; text-decoration: none !important;
        margin: 0 .55rem;
    }

    @media (max-width: 700px) {
        body.tl-login-page .tl-hero { display: none; }
        body.tl-login-page .tl-form-side { padding: 2rem 1.5rem; }
    }
</style>
@endpush

{{-- Inyectar clase en el body via JS al cargar — sin tocar otras páginas --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.body.classList.add('tl-login-page');
    });
    // Por si DOMContentLoaded ya pasó (Livewire SPA)
    document.body.classList.add('tl-login-page');
</script>

<div class="tl-login-wrap">

    {{-- ══ IZQUIERDA ══ --}}
    <div class="tl-hero">
        <div class="tl-hero-fallback"></div>
        <div class="tl-hero-bg"></div>
        <div class="tl-hero-grid"></div>

        <div class="tl-logo">
            <div class="tl-logo-icon">
                <svg viewBox="0 0 24 24">
                    <path d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h6c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4zM6 18.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm13.5-9l1.96 2.5H17V9.5h2.5zm-1.5 9c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/>
                </svg>
            </div>
            <span class="tl-logo-name">TraceLog</span>
        </div>

        <div class="tl-hero-bottom">
            <div class="tl-stats">
                <div class="tl-stat"><strong>99%</strong><span>Entregas</span></div>
                <div class="tl-stat"><strong>24/7</strong><span>Trazabilidad</span></div>
                <div class="tl-stat"><strong>+500</strong><span>Empresas</span></div>
            </div>
            <div class="tl-tagline">
                <h2>Cadena de suministro bajo control total.</h2>
                <p>Gestiona proveedores, inventario, pedidos y entregas con trazabilidad en tiempo real.</p>
            </div>
        </div>
    </div>

    {{-- ══ DERECHA: Formulario ══ --}}
    <div class="tl-form-side">
        <div class="tl-form-box">

            <h1>Bienvenido de nuevo</h1>
            <p class="tl-sub">Ingresa tus credenciales para acceder al sistema.</p>

            {{ \Filament\Support\Facades\FilamentView::renderHook('panels::auth.login.form.before') }}

            <x-filament-panels::form id="form" wire:submit="authenticate">
                {{ $this->form }}
                <x-filament-panels::form.actions
                    :actions="$this->getCachedFormActions()"
                    :full-width="$this->hasFullWidthFormActions()"
                />
            </x-filament-panels::form>

            <div class="tl-sep">
                <hr><span>O continúa con</span><hr>
            </div>

            <div class="tl-oauth">
                <a href="#" class="tl-oauth-btn">
                    <svg viewBox="0 0 24 24">
                        <path d="M12.48 10.92v3.28h7.84c-.24 1.84-.853 3.187-1.787 4.133-1.147 1.147-2.933 2.4-6.053 2.4-4.827 0-8.6-3.893-8.6-8.72s3.773-8.72 8.6-8.72c2.6 0 4.507 1.027 5.907 2.347l2.307-2.307C18.747 1.44 16.133 0 12.48 0 5.867 0 .307 5.387.307 12s5.56 12 12.173 12c3.573 0 6.267-1.173 8.373-3.36 2.16-2.16 2.84-5.213 2.84-7.667 0-.76-.053-1.467-.173-2.053H12.48z" fill="#4285F4"/>
                    </svg>
                    Google
                </a>
                <a href="#" class="tl-oauth-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#1a2e1a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/>
                    </svg>
                    API Key
                </a>
            </div>

            @if (filament()->hasRegistration())
                <div class="tl-reg">
                    ¿No tienes cuenta?
                    <a href="{{ filament()->getRegistrationUrl() }}">Crear cuenta</a>
                </div>
            @else
                <div class="tl-reg">¿No tienes cuenta? <a href="#">Solicitar acceso</a></div>
            @endif

            <div class="tl-footer">
                <a href="#">Privacidad</a>
                <a href="#">Términos</a>
                <a href="#">Soporte</a>
            </div>

            {{ \Filament\Support\Facades\FilamentView::renderHook('panels::auth.login.form.after') }}
        </div>
    </div>

</div>

{{-- Limpiar la clase del body al salir de esta página (navegación Livewire) --}}
<script>
    document.addEventListener('livewire:navigating', function () {
        document.body.classList.remove('tl-login-page');
    });
</script>