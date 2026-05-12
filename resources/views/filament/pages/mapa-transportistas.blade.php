<x-filament-panels::page>
<div class="space-y-4">

    {{-- ── KPIs ── --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

        <x-filament::section>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        Total
                    </p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-0.5">
                        {{ $this->todosTransportistas->count() }}
                    </p>
                </div>
                <div class="p-2.5 rounded-xl bg-primary-100 dark:bg-primary-900/30">
                    <x-heroicon-m-truck class="w-6 h-6 text-primary-600 dark:text-primary-400"/>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        Disponibles
                    </p>
                    <p class="text-2xl font-bold text-success-600 dark:text-success-400 mt-0.5">
                        {{ $this->transportistasDisponibles->count() }}
                    </p>
                </div>
                <div class="p-2.5 rounded-xl bg-success-100 dark:bg-success-900/30">
                    <x-heroicon-m-check-circle class="w-6 h-6 text-success-600 dark:text-success-400"/>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        En Ruta
                    </p>
                    <p class="text-2xl font-bold text-warning-600 dark:text-warning-400 mt-0.5">
                        {{ $this->transportistasEnRuta->count() }}
                    </p>
                </div>
                <div class="p-2.5 rounded-xl bg-warning-100 dark:bg-warning-900/30">
                    <x-heroicon-m-map class="w-6 h-6 text-warning-600 dark:text-warning-400"/>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        Mantenimiento
                    </p>
                    <p class="text-2xl font-bold text-danger-600 dark:text-danger-400 mt-0.5">
                        {{ $this->transportistasMantenimiento->count() }}
                    </p>
                </div>
                <div class="p-2.5 rounded-xl bg-danger-100 dark:bg-danger-900/30">
                    <x-heroicon-m-wrench-screwdriver class="w-6 h-6 text-danger-600 dark:text-danger-400"/>
                </div>
            </div>
        </x-filament::section>

    </div>

    {{-- ── Leyenda + controles ── --}}
    <x-filament::section>
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-wrap gap-5">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-success-500 flex-shrink-0"></span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Disponible</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-warning-500 flex-shrink-0"></span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">En Ruta</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-danger-500 flex-shrink-0"></span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Mantenimiento</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-gray-400 animate-pulse flex-shrink-0"></span>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Sin ubicación</span>
                </div>
            </div>
            <x-filament::button
                onclick="centrarMapa()"
                icon="heroicon-m-map-pin"
                color="gray"
                size="sm"
            >
                Centrar mapa
            </x-filament::button>
        </div>
    </x-filament::section>

    {{-- ── Mapa Leaflet ── --}}
    <x-filament::section>
        <div id="map" style="height: 520px; width: 100%; border-radius: 8px; z-index: 0;"></div>
    </x-filament::section>

    {{-- ── Transportistas en ruta ── --}}
    <x-filament::section>
        <x-slot name="heading">
            <span class="flex items-center gap-2">
                <x-heroicon-m-truck class="w-4 h-4 text-warning-500"/>
                Camiones en Ruta
                <span class="bg-warning-100 dark:bg-warning-900/30 text-warning-700 dark:text-warning-400
                             text-xs font-bold px-2 py-0.5 rounded-full">
                    {{ $this->transportistasEnRuta->count() }}
                </span>
            </span>
        </x-slot>

        @if($this->transportistasEnRuta->isEmpty())
            <div class="text-center py-10 text-gray-400">
                <x-heroicon-m-truck class="w-10 h-10 mx-auto mb-2 opacity-30"/>
                <p class="text-sm">No hay camiones en ruta en este momento</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($this->transportistasEnRuta as $transportista)
                    <div class="rounded-xl border-l-4 border-warning-400
                                bg-warning-50 dark:bg-warning-900/10
                                border border-warning-200 dark:border-warning-800
                                p-4 transition hover:shadow-md">
                        <div class="flex justify-between items-start gap-2">
                            <div class="min-w-0">
                                <p class="font-semibold text-sm text-gray-800 dark:text-gray-200 truncate">
                                    {{ $transportista->nombre }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    {{ $transportista->vehiculo_tipo }} — {{ $transportista->vehiculo_placa }}
                                </p>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-2 space-y-0.5">
                                    <p class="flex items-center gap-1">
                                        <x-heroicon-m-user class="w-3 h-3 flex-shrink-0"/>
                                        {{ $transportista->conductor_nombre ?? 'Sin conductor asignado' }}
                                    </p>
                                    <p class="flex items-center gap-1">
                                        <x-heroicon-m-phone class="w-3 h-3 flex-shrink-0"/>
                                        {{ $transportista->conductor_telefono ?? $transportista->telefono ?? 'Sin teléfono' }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex-shrink-0 text-right">
                                <span class="inline-block px-2 py-0.5 text-xs font-semibold rounded-full
                                             bg-warning-100 dark:bg-warning-900/30
                                             text-warning-700 dark:text-warning-400">
                                    En Ruta
                                </span>
                                @if($transportista->ultima_ubicacion_at)
                                    <p class="text-xs text-gray-400 mt-1">
                                        {{ $transportista->ultima_ubicacion_at->diffForHumans() }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        @if($transportista->latitud && $transportista->longitud)
                            <div class="mt-3 pt-3 border-t border-warning-200 dark:border-warning-800">
                                <p class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1 mb-1">
                                    <x-heroicon-m-map-pin class="w-3 h-3 flex-shrink-0"/>
                                    {{ $transportista->ubicacion_actual ?? number_format($transportista->latitud,4).', '.number_format($transportista->longitud,4) }}
                                </p>
                                <button onclick="irAUbicacion({{ $transportista->latitud }}, {{ $transportista->longitud }})"
                                        class="text-xs text-warning-600 dark:text-warning-400
                                               hover:text-warning-800 dark:hover:text-warning-300
                                               flex items-center gap-1 transition font-medium">
                                    <x-heroicon-m-map-pin class="w-3 h-3"/>
                                    Ver en el mapa
                                </button>
                            </div>
                        @else
                            <div class="mt-3 pt-3 border-t border-warning-200 dark:border-warning-800">
                                <p class="text-xs text-gray-400 flex items-center gap-1">
                                    <x-heroicon-m-exclamation-triangle class="w-3 h-3"/>
                                    Sin ubicación GPS registrada
                                </p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::section>

    {{-- ── Tabla todos los transportistas ── --}}
    <x-filament::section>
        <x-slot name="heading">
            <span class="flex items-center gap-2">
                <x-heroicon-m-list-bullet class="w-4 h-4 text-gray-400"/>
                Todos los Transportistas
                <span class="bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300
                             text-xs font-bold px-2 py-0.5 rounded-full">
                    {{ $this->todosTransportistas->count() }}
                </span>
            </span>
        </x-slot>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="text-left py-2.5 px-3 text-xs font-semibold uppercase tracking-wide
                                   text-gray-500 dark:text-gray-400">Nombre</th>
                        <th class="text-left py-2.5 px-3 text-xs font-semibold uppercase tracking-wide
                                   text-gray-500 dark:text-gray-400">Vehículo</th>
                        <th class="text-left py-2.5 px-3 text-xs font-semibold uppercase tracking-wide
                                   text-gray-500 dark:text-gray-400">Conductor</th>
                        <th class="text-center py-2.5 px-3 text-xs font-semibold uppercase tracking-wide
                                   text-gray-500 dark:text-gray-400">Estado</th>
                        <th class="text-center py-2.5 px-3 text-xs font-semibold uppercase tracking-wide
                                   text-gray-500 dark:text-gray-400">Ubicación</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($this->todosTransportistas as $transportista)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition">
                            <td class="py-3 px-3 font-medium text-gray-800 dark:text-gray-200">
                                {{ $transportista->nombre }}
                            </td>
                            <td class="py-3 px-3 text-gray-600 dark:text-gray-400">
                                {{ $transportista->vehiculo_tipo }}
                                <span class="text-gray-400">({{ $transportista->vehiculo_placa }})</span>
                            </td>
                            <td class="py-3 px-3 text-gray-600 dark:text-gray-400">
                                {{ $transportista->conductor_nombre ?? '—' }}
                            </td>
                            <td class="py-3 px-3 text-center">
                                <span @class([
                                    'inline-block px-2 py-0.5 text-xs font-semibold rounded-full',
                                    'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400'
                                        => $transportista->estado === 'disponible',
                                    'bg-warning-100 text-warning-700 dark:bg-warning-900/30 dark:text-warning-400'
                                        => $transportista->estado === 'en_ruta',
                                    'bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-400'
                                        => $transportista->estado === 'mantenimiento',
                                    'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300'
                                        => !in_array($transportista->estado, ['disponible','en_ruta','mantenimiento']),
                                ])>
                                    {{ match($transportista->estado) {
                                        'disponible'    => 'Disponible',
                                        'en_ruta'       => 'En Ruta',
                                        'mantenimiento' => 'Mantenimiento',
                                        'inactivo'      => 'Inactivo',
                                        default         => ucfirst($transportista->estado),
                                    } }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-center">
                                @if($transportista->latitud && $transportista->longitud)
                                    <button
                                        onclick="irAUbicacion({{ $transportista->latitud }}, {{ $transportista->longitud }})"
                                        class="inline-flex items-center gap-1 text-xs text-primary-600
                                               dark:text-primary-400 hover:underline font-medium"
                                    >
                                        <x-heroicon-m-map-pin class="w-3 h-3"/>
                                        Ver
                                    </button>
                                @else
                                    <span class="text-gray-300 dark:text-gray-600">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-filament::section>

</div>
</x-filament-panels::page>

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<style>
    #map { z-index: 0; }
    .leaflet-popup-content-wrapper {
        border-radius: 10px;
        font-family: inherit;
        box-shadow: 0 4px 16px rgba(0,0,0,.12);
    }
    .leaflet-popup-content { margin: 12px 14px; }
    .tl-marker {
        width: 34px; height: 34px;
        border-radius: 50% 50% 50% 0;
        border: 2px solid #fff;
        box-shadow: 0 2px 6px rgba(0,0,0,.25);
        transform: rotate(-45deg);
        display: flex; align-items: center; justify-content: center;
        transition: transform .2s;
    }
    .tl-marker:hover { transform: rotate(-45deg) scale(1.15); }
    .tl-marker-icon {
        transform: rotate(45deg);
        display: flex; align-items: center; justify-content: center;
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    let map, markers = [];

    document.addEventListener('DOMContentLoaded', initMap);

    function initMap() {
        map = L.map('map').setView([13.6929, -89.2182], 8);

        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; OpenStreetMap &copy; CARTO',
            subdomains: 'abcd', maxZoom: 19, minZoom: 5
        }).addTo(map);

        cargarMarcadores();
    }

    function colorEstado(estado) {
        return { disponible: '#22c55e', en_ruta: '#f59e0b', mantenimiento: '#ef4444' }[estado] ?? '#9ca3af';
    }

    function etiquetaEstado(estado) {
        return { disponible: 'Disponible', en_ruta: 'En Ruta', mantenimiento: 'Mantenimiento' }[estado] ?? estado;
    }

    function cargarMarcadores() {
        markers.forEach(m => map.removeLayer(m));
        markers = [];

        @json($this->todosTransportistas).forEach(function(t) {
            if (!t.latitud || !t.longitud) return;

            const color = colorEstado(t.estado);

            const icon = L.divIcon({
                className: '',
                html: `<div class="tl-marker" style="background:${color}">
                           <div class="tl-marker-icon">
                               <svg width="16" height="16" fill="white" viewBox="0 0 24 24">
                                   <path d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h6c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4zM6 18.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm13.5-9l1.96 2.5H17V9.5h2.5zm-1.5 9c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/>
                               </svg>
                           </div>
                       </div>`,
                iconSize: [34, 34],
                popupAnchor: [0, -18]
            });

            const marker = L.marker([+t.latitud, +t.longitud], { icon }).addTo(map);

            marker.bindPopup(`
                <div style="min-width:210px;font-size:13px;">
                    <p style="font-weight:700;font-size:14px;border-bottom:1px solid #eee;
                               padding-bottom:6px;margin-bottom:8px;">${t.nombre}</p>
                    <p style="color:#555;margin:3px 0;">
                        <strong>Vehiculo:</strong> ${t.vehiculo_tipo ?? '—'} (${t.vehiculo_placa ?? '—'})
                    </p>
                    <p style="color:#555;margin:3px 0;">
                        <strong>Conductor:</strong> ${t.conductor_nombre ?? 'N/A'}
                    </p>
                    <p style="color:#555;margin:3px 0;">
                        <strong>Tel:</strong> ${t.conductor_telefono ?? t.telefono ?? 'N/A'}
                    </p>
                    <p style="color:#555;margin:3px 0;">
                        <strong>Ubicacion:</strong> ${t.ubicacion_actual ?? `${(+t.latitud).toFixed(4)}, ${(+t.longitud).toFixed(4)}`}
                    </p>
                    <p style="margin-top:8px;">
                        <span style="background:${color};color:#fff;padding:2px 8px;
                                     border-radius:999px;font-size:11px;font-weight:600;">
                            ${etiquetaEstado(t.estado)}
                        </span>
                    </p>
                </div>
            `);

            markers.push(marker);
        });

        if (markers.length > 0) {
            map.fitBounds(L.featureGroup(markers).getBounds().pad(0.1));
        }
    }

    function centrarMapa() {
        if (markers.length > 0) {
            map.fitBounds(L.featureGroup(markers).getBounds().pad(0.1));
        } else {
            map.setView([13.6929, -89.2182], 8);
        }
    }

    function irAUbicacion(lat, lng) {
        map.setView([+lat, +lng], 14);
        markers.find(m => {
            const ll = m.getLatLng();
            return Math.abs(ll.lat - lat) < 0.0001 && Math.abs(ll.lng - lng) < 0.0001;
        })?.openPopup();
    }
</script>
@endpush