<style>
    /* ── Neutralizar cualquier margen del body/html que ponga Filament ── */
    html, body {
        margin: 0 !important;
        padding: 0 !important;
        height: 100% !important;
        background: #0d2b0d !important;
    }

    /* ── Variables TraceLog ── */
    :root {
        --tl-green-deep:   #0d2b0d;
        --tl-green-dark:   #1a4a1a;
        --tl-green-mid:    #2d6a2d;
        --tl-green-accent: #3d8b3d;
        --tl-green-bright: #4caf50;
        --tl-warm-white:   #fefefe;
        --tl-text-dark:    #1a2e1a;
        --tl-text-muted:   #6b7c6b;
        --tl-border:       #d8e5d8;
        --tl-input-bg:     #f4f8f4;
    }

    /* ── Wrapper principal: ocupa toda la pantalla ── */
    .tl-login-wrap {
        display: flex;
        width: 100vw;
        min-height: 100dvh;
        font-family: Georgia, 'Times New Roman', serif;
        position: fixed;
        top: 0;
        left: 0;
        z-index: 0;
    }

    /* ══ PANEL IZQUIERDO ══════════════════════════════════ */
    .tl-hero {
        position: relative;
        width: 45%;
        min-height: 100dvh;
        overflow: hidden;
        flex-shrink: 0;
    }

    .tl-hero-bg {
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

    .tl-hero-fallback {
        position: absolute;
        inset: 0;
        background: linear-gradient(160deg, #0d2b0d 0%, #1a4a1a 40%, #2d6a2d 100%);
    }

    .tl-hero-grid {
        position: absolute;
        inset: 0;
        background-image:
            linear-gradient(rgba(77,175,80,.05) 1px, transparent 1px),
            linear-gradient(90deg, rgba(77,175,80,.05) 1px, transparent 1px);
        background-size: 40px 40px;
        z-index: 1;
    }

    /* Logo */
    .tl-logo {
        position: absolute;
        top: 1.8rem;
        left: 2rem;
        z-index: 10;
        display: flex;
        align-items: center;
        gap: .6rem;
    }

    .tl-logo-icon {
        width: 34px; height: 34px;
        background: rgba(77,175,80,.9);
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
    }

    .tl-logo-icon svg { width: 20px; height: 20px; fill: #fff; }
    .tl-logo-name { color: #fff; font-size: 1.05rem; font-weight: 700; text-shadow: 0 1px 4px rgba(0,0,0,.45); }

    /* Stats + tagline */
    .tl-hero-bottom {
        position: absolute;
        bottom: 2.5rem; left: 2rem; right: 2rem;
        z-index: 10;
    }

    .tl-stats { display: flex; gap: .65rem; margin-bottom: 1.3rem; }

    .tl-stat {
        background: rgba(255,255,255,.12);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,.2);
        border-radius: 8px;
        padding: .45rem .75rem;
        text-align: center;
    }

    .tl-stat strong { display: block; color: #fff; font-size: 1rem; font-weight: 700; }
    .tl-stat span   { display: block; color: rgba(255,255,255,.62); font-size: .62rem;
                       font-family: Arial, sans-serif; text-transform: uppercase;
                       letter-spacing: .07em; margin-top: 1px; }

    .tl-tagline h2 {
        color: #fff; font-size: 2rem; font-weight: 700;
        line-height: 1.2; margin: 0 0 .7rem;
        text-shadow: 0 2px 12px rgba(0,0,0,.55);
    }

    .tl-tagline p {
        color: rgba(255,255,255,.72); font-size: .8rem;
        line-height: 1.55; font-family: Arial, sans-serif;
        margin: 0; text-shadow: 0 1px 6px rgba(0,0,0,.5);
    }

    /* ══ PANEL DERECHO ════════════════════════════════════ */
    .tl-form-side {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--tl-warm-white);
        padding: 2.5rem 3rem;
        overflow-y: auto;
        min-height: 100dvh;
    }

    .tl-form-box { width: 100%; max-width: 360px; }

    /* Encabezado */
    .tl-form-box h1 {
        font-family: Georgia, serif;
        font-size: 1.8rem; font-weight: 700;
        color: var(--tl-text-dark);
        margin: 0 0 .35rem; letter-spacing: -.01em;
    }

    .tl-form-box .tl-sub {
        font-family: Arial, sans-serif; font-size: .845rem;
        color: var(--tl-text-muted); margin: 0 0 1.75rem;
    }

    /* ── Sobreescribir Filament form ── */
    .tl-form-box .fi-fo-component-ctn,
    .tl-form-box .fi-fo-field-wrp { margin-bottom: .9rem !important; }

    .tl-form-box label {
        font-family: Arial, sans-serif !important;
        font-size: .7rem !important;
        font-weight: 700 !important;
        color: var(--tl-text-dark) !important;
        text-transform: uppercase !important;
        letter-spacing: .07em !important;
    }

    /* Inputs */
    .tl-form-box input[type="email"],
    .tl-form-box input[type="password"],
    .tl-form-box input[type="text"] {
        font-family: Arial, sans-serif !important;
        font-size: .9rem !important;
        background: var(--tl-input-bg) !important;
        border: 1.5px solid var(--tl-border) !important;
        border-radius: 8px !important;
        padding: .65rem .9rem !important;
        color: var(--tl-text-dark) !important;
        width: 100% !important;
        box-sizing: border-box !important;
        transition: border-color .2s, box-shadow .2s !important;
    }

    .tl-form-box input[type="email"]:focus,
    .tl-form-box input[type="password"]:focus,
    .tl-form-box input[type="text"]:focus {
        outline: none !important;
        border-color: var(--tl-green-accent) !important;
        box-shadow: 0 0 0 3px rgba(77,175,80,.12) !important;
        background: #fff !important;
    }

    /* Checkbox */
    .tl-form-box input[type="checkbox"] { accent-color: var(--tl-green-accent) !important; }

    /* Forgot password link */
    .tl-form-box a { color: var(--tl-green-mid) !important; font-family: Arial, sans-serif !important; font-size: .82rem !important; }
    .tl-form-box a:hover { color: var(--tl-green-accent) !important; }

    /* Botón submit */
    .tl-form-box button[type="submit"] {
        background: var(--tl-green-dark) !important;
        border: none !important;
        border-radius: 8px !important;
        color: #fff !important;
        font-family: Arial, sans-serif !important;
        font-size: .88rem !important;
        font-weight: 600 !important;
        letter-spacing: .04em !important;
        padding: .75rem 1.5rem !important;
        width: 100% !important;
        cursor: pointer !important;
        transition: background .2s, transform .1s !important;
    }

    .tl-form-box button[type="submit"]:hover { background: var(--tl-green-mid) !important; transform: translateY(-1px) !important; }
    .tl-form-box button[type="submit"]:active { transform: translateY(0) !important; }

    /* Separador */
    .tl-sep {
        display: flex; align-items: center; gap: .7rem;
        margin: 1.4rem 0;
    }

    .tl-sep hr { flex: 1; border: none; border-top: 1px solid var(--tl-border); margin: 0; }
    .tl-sep span {
        font-family: Arial, sans-serif; font-size: .68rem;
        color: var(--tl-text-muted); text-transform: uppercase;
        letter-spacing: .1em; white-space: nowrap;
    }

    /* Botones OAuth */
    .tl-oauth { display: grid; grid-template-columns: 1fr 1fr; gap: .6rem; margin-bottom: 1.5rem; }

    .tl-oauth-btn {
        display: flex; align-items: center; justify-content: center;
        gap: .45rem;
        padding: .58rem .8rem;
        border: 1.5px solid var(--tl-border);
        border-radius: 8px;
        background: #fff;
        color: var(--tl-text-dark) !important;
        font-family: Arial, sans-serif !important;
        font-size: .8rem !important;
        font-weight: 500;
        text-decoration: none !important;
        cursor: pointer;
        transition: border-color .18s, background .18s;
    }

    .tl-oauth-btn:hover {
        border-color: var(--tl-green-accent) !important;
        background: var(--tl-input-bg) !important;
        color: var(--tl-text-dark) !important;
    }

    .tl-oauth-btn svg { width: 15px; height: 15px; flex-shrink: 0; }

    /* Registro y footer */
    .tl-reg {
        text-align: center;
        font-family: Arial, sans-serif; font-size: .81rem;
        color: var(--tl-text-muted); margin-bottom: 1.2rem;
    }

    .tl-reg a { color: var(--tl-green-dark) !important; font-weight: 600 !important; text-decoration: none !important; }
    .tl-reg a:hover { text-decoration: underline !important; }

    .tl-footer {
        text-align: center; padding-top: 1.1rem;
        border-top: 1px solid var(--tl-border);
    }

    .tl-footer a {
        font-family: Arial, sans-serif !important; font-size: .72rem !important;
        color: var(--tl-text-muted) !important; text-decoration: none !important;
        margin: 0 .55rem;
    }

    .tl-footer a:hover { color: var(--tl-green-accent) !important; }

    /* Responsive */
    @media (max-width: 700px) {
        .tl-hero { display: none; }
        .tl-form-side { padding: 2rem 1.5rem; }
    }
    /* Para los inputs y su texto */
.fi-input,
.fi-input input,
.fi-input input::placeholder {
    color: #000000 !important;
}

/* Para las etiquetas */
.fi-fo-field-wrp label {
    color: #000000 !important;
}
</style>

<div class="tl-login-wrap">
    <div class="tl-hero">
        <div class="tl-hero-fallback"></div>
        <div class="tl-hero-bg"></div>
        <div class="tl-hero-grid"></div>

        <div class="tl-logo absolute top-margin left-margin flex items-center gap-xs">
            <div class="">
                <img src="{{ asset('images/logo.png') }}" alt="Agro Alvarado" class="h-10 w-auto">
            </div>
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

            <x-filament-panels::form id="form" wire:submit="authenticate" class="text-color black">
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
                    <svg viewBox="0 0 24 24"><path d="M12.48 10.92v3.28h7.84c-.24 1.84-.853 3.187-1.787 4.133-1.147 1.147-2.933 2.4-6.053 2.4-4.827 0-8.6-3.893-8.6-8.72s3.773-8.72 8.6-8.72c2.6 0 4.507 1.027 5.907 2.347l2.307-2.307C18.747 1.44 16.133 0 12.48 0 5.867 0 .307 5.387.307 12s5.56 12 12.173 12c3.573 0 6.267-1.173 8.373-3.36 2.16-2.16 2.84-5.213 2.84-7.667 0-.76-.053-1.467-.173-2.053H12.48z" fill="#4285F4"/></svg>
                    Google
                </a>
                <a href="#" class="tl-oauth-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#1a2e1a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>
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