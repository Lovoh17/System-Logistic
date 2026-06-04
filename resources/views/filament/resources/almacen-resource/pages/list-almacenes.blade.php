<x-filament-panels::page>
    @php
        $todas     = $this->getTodas();
        $conCoords = $this->getConCoordenadas();
        $activas   = $todas->where('activo', true)->count();
        $sinGps    = $todas->filter(fn($a) => !$a->tieneCoordenadas())->count();
    @endphp

    <div class="space-y-4">

        <div class="flex gap-4">

            <div class="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-5 h-24">
                <div class="flex items-center justify-between h-full">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Total Sucursales
                        </p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-0.5">
                            {{ $todas->count() }}
                        </p>
                    </div>
                    <div class="p-2.5 rounded-xl bg-primary-100 dark:bg-primary-900/30">
                        <x-heroicon-m-building-storefront class="w-6 h-6 text-primary-600 dark:text-primary-400"/>
                    </div>
                </div>
            </div>

            <div class="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-5 h-24">
                <div class="flex items-center justify-between h-full">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Activas
                        </p>
                        <p class="text-2xl font-bold text-success-600 dark:text-success-400 mt-0.5">
                            {{ $activas }}
                        </p>
                    </div>
                    <div class="p-2.5 rounded-xl bg-success-100 dark:bg-success-900/30">
                        <x-heroicon-m-check-circle class="w-6 h-6 text-success-600 dark:text-success-400"/>
                    </div>
                </div>
            </div>

            <div class="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-5 h-24">
                <div class="flex items-center justify-between h-full">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Ubicadas en Mapa
                        </p>
                        <p class="text-2xl font-bold text-info-600 dark:text-info-400 mt-0.5">
                            {{ $conCoords->count() }}
                        </p>
                    </div>
                    <div class="p-2.5 rounded-xl bg-info-100 dark:bg-info-900/30">
                        <x-heroicon-m-map-pin class="w-6 h-6 text-info-600 dark:text-info-400"/>
                    </div>
                </div>
            </div>

            <div class="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-5 h-24">
                <div class="flex items-center justify-between h-full">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Sin GPS
                        </p>
                        <p @class([
                            'text-2xl font-bold mt-0.5',
                            'text-warning-600 dark:text-warning-400' => $sinGps > 0,
                            'text-gray-400 dark:text-gray-500'       => $sinGps === 0,
                        ])>
                            {{ $sinGps }}
                        </p>
                    </div>
                    <div @class([
                        'p-2.5 rounded-xl',
                        'bg-warning-100 dark:bg-warning-900/30' => $sinGps > 0,
                        'bg-gray-100 dark:bg-gray-700'          => $sinGps === 0,
                    ])>
                        <x-heroicon-m-exclamation-triangle @class([
                            'w-6 h-6',
                            'text-warning-600 dark:text-warning-400' => $sinGps > 0,
                            'text-gray-400 dark:text-gray-500'       => $sinGps === 0,
                        ])/>
                    </div>
                </div>
            </div>

        </div>

        <x-filament::section>

            <x-slot name="heading">
                <span class="flex items-center gap-2">
                    <x-heroicon-m-map-pin class="w-4 h-4 text-info-500"/>
                    Mapa de Sucursales
                    @if($conCoords->count() > 0)
                        <span class="bg-info-100 dark:bg-info-900/30 text-info-700 dark:text-info-400
                                     text-xs font-bold px-2 py-0.5 rounded-full">
                            {{ $conCoords->count() }} de {{ $todas->count() }} ubicadas
                        </span>
                    @endif
                </span>
            </x-slot>

            <x-slot name="headerEnd">
                <div class="flex flex-wrap items-center gap-4">
                    <div class="flex flex-wrap gap-4">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-success-500 flex-shrink-0"></span>
                            <span class="text-sm text-gray-600 dark:text-gray-400">Principal</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-info-500 flex-shrink-0"></span>
                            <span class="text-sm text-gray-600 dark:text-gray-400">Sucursal</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-gray-400 flex-shrink-0"></span>
                            <span class="text-sm text-gray-600 dark:text-gray-400">Inactiva</span>
                        </div>
                    </div>
                    <x-filament::button
                            onclick="centrarMapaSucursales()"
                            icon="heroicon-m-viewfinder-circle"
                            color="gray"
                            size="sm"
                    >
                        Centrar mapa
                    </x-filament::button>
                </div>
            </x-slot>

            @if($conCoords->count() === 0)
                <div class="text-center py-14 text-gray-400">
                    <x-heroicon-o-map class="w-14 h-14 mx-auto mb-4 opacity-20"/>
                    <p class="font-medium text-gray-600 dark:text-gray-400">
                        Ninguna sucursal tiene coordenadas aún
                    </p>
                    <p class="text-sm text-gray-400 mt-1 max-w-sm mx-auto">
                        Edite una sucursal, vaya a
                        <strong class="font-semibold text-gray-500 dark:text-gray-300">Ubicación Geográfica</strong>
                        y añada su latitud y longitud para verla aquí.
                    </p>
                    @if($sinGps > 0)
                        <p class="text-xs text-gray-400 mt-3">
                            También puede ejecutar
                            <code class="bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded text-gray-600 dark:text-gray-300">
                                php artisan sucursales:seed-coordenadas
                            </code>
                            para poblar coordenadas automáticamente.
                        </p>
                    @endif
                </div>
            @else
                <div id="mapa-sucursales" style="height: 460px; width: 100%; border-radius: 8px; z-index: 0;"></div>

                @if($sinGps > 0)
                    <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-800
                                flex items-center gap-1.5 text-xs text-warning-600 dark:text-warning-400">
                        <x-heroicon-m-exclamation-triangle class="w-3.5 h-3.5 flex-shrink-0"/>
                        {{ $sinGps }} {{ $sinGps === 1 ? 'sucursal sin' : 'sucursales sin' }} coordenadas — edítelas para ubicarlas.
                    </div>
                @else
                    <p class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-800 text-xs text-gray-400">
                        Haga clic en un marcador para ver los detalles de la sucursal.
                    </p>
                @endif
            @endif

        </x-filament::section>

        {{ $this->table }}

    </div>
</x-filament-panels::page>

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <style>
        #mapa-sucursales { z-index: 0; }
        .leaflet-popup-content-wrapper { border-radius: 10px; font-family: inherit; box-shadow: 0 4px 16px rgba(0,0,0,.12); }
        .leaflet-popup-content { margin: 12px 14px; }
        .suc-pin {
            width: 34px; height: 34px;
            border-radius: 50% 50% 50% 0;
            border: 2px solid #fff;
            box-shadow: 0 2px 6px rgba(0,0,0,.25);
            transform: rotate(-45deg);
            display: flex; align-items: center; justify-content: center;
            transition: transform .2s, box-shadow .2s;
        }
        .suc-pin:hover { transform: rotate(-45deg) scale(1.15); box-shadow: 0 4px 12px rgba(0,0,0,.32); }
        .suc-pin-inner { transform: rotate(45deg); font-size: 11px; font-weight: 800; color: #fff; letter-spacing: -.3px; }
    </style>
@endpush

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        (function () {
            let mapInst, markers = [];
            const SUCURSALES = @json($this->getConCoordenadas());

            function initMapa() {
                const el = document.getElementById('mapa-sucursales');
                if (!el || mapInst) return;
                mapInst = L.map('mapa-sucursales', { zoomControl: true }).setView([13.6929, -88.8960], 9);
                L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                    attribution: '&copy; OpenStreetMap &copy; CARTO',
                    subdomains: 'abcd', maxZoom: 19, minZoom: 4,
                }).addTo(mapInst);
                cargarMarcadores();
            }

            function colorSucursal(s) {
                if (s.es_principal) return '#22c55e';
                if (s.activo)       return '#3b82f6';
                return '#9ca3af';
            }

            function labelSucursal(s) {
                if (s.es_principal) return 'P';
                return (s.codigo ?? 'S').substring(0, 2).toUpperCase();
            }

            function cargarMarcadores() {
                markers.forEach(m => mapInst.removeLayer(m));
                markers = [];
                SUCURSALES.forEach(function (s) {
                    if (!s.latitud || !s.longitud) return;
                    const color = colorSucursal(s);
                    const icon = L.divIcon({
                        className: '',
                        html: `<div class="suc-pin" style="background:${color}"><div class="suc-pin-inner">${labelSucursal(s)}</div></div>`,
                        iconSize: [34, 34], iconAnchor: [17, 34], popupAnchor: [0, -38],
                    });
                    const m = L.marker([+s.latitud, +s.longitud], { icon }).addTo(mapInst);
                    const badge = s.es_principal
                        ? `<span style="background:#22c55e;color:#fff;padding:2px 8px;border-radius:999px;font-size:11px;font-weight:600;">Principal</span>`
                        : (s.activo
                            ? `<span style="background:#3b82f6;color:#fff;padding:2px 8px;border-radius:999px;font-size:11px;font-weight:600;">Sucursal Activa</span>`
                            : `<span style="background:#9ca3af;color:#fff;padding:2px 8px;border-radius:999px;font-size:11px;font-weight:600;">Inactiva</span>`);
                    m.bindPopup(`
                <div style="min-width:220px;font-size:13px;line-height:1.55;">
                    <p style="font-weight:700;font-size:14px;border-bottom:1px solid #eee;padding-bottom:6px;margin-bottom:8px;">${s.nombre}</p>
                    <p style="color:#555;margin:3px 0;"><strong>Código:</strong> ${s.codigo}</p>
                    ${s.direccion   ? `<p style="color:#555;margin:3px 0;"><strong>Dirección:</strong> ${s.direccion}</p>`     : ''}
                    ${s.responsable ? `<p style="color:#555;margin:3px 0;"><strong>Responsable:</strong> ${s.responsable}</p>` : ''}
                    ${s.telefono    ? `<p style="color:#555;margin:3px 0;"><strong>Teléfono:</strong> ${s.telefono}</p>`       : ''}
                    <p style="color:#555;margin:3px 0;font-family:monospace;font-size:11px;">${(+s.latitud).toFixed(5)}, ${(+s.longitud).toFixed(5)}</p>
                    <div style="margin-top:8px;">${badge}</div>
                </div>
            `);
                    markers.push(m);
                });
                if (markers.length > 0) mapInst.fitBounds(L.featureGroup(markers).getBounds().pad(0.18));
            }

            window.centrarMapaSucursales = function () {
                if (!mapInst) return;
                markers.length > 0
                    ? mapInst.fitBounds(L.featureGroup(markers).getBounds().pad(0.18))
                    : mapInst.setView([13.6929, -88.8960], 9);
            };

            window.irASucursal = function (lat, lng) {
                if (!mapInst) return;
                mapInst.setView([+lat, +lng], 15);
                const m = markers.find(mk => {
                    const ll = mk.getLatLng();
                    return Math.abs(ll.lat - lat) < 0.0001 && Math.abs(ll.lng - lng) < 0.0001;
                });
                if (m) m.openPopup();
            };

            document.addEventListener('DOMContentLoaded', initMapa);
            document.addEventListener('livewire:navigated', function () { mapInst = null; markers = []; initMapa(); });
        })();
    </script>
@endpush