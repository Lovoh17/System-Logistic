<x-filament-panels::page>

    @php
        $traslado      = $this->record;
        $origen        = $traslado->almacenOrigen;
        $destino       = $traslado->almacenDestino;
        $transportista = $traslado->transportista;
        $conductor     = $transportista?->user;
        $distanciaKm   = $this->getDistanciaKm();
        $costo         = $this->getCostoEstimado();

        $estadoColor = match($traslado->estado) {
            'sugerido'   => 'warning',
            'aprobado'   => 'info',
            'completado' => 'success',
            'cancelado'  => 'danger',
            default      => 'gray',
        };
        $estadoLabel = match($traslado->estado) {
            'sugerido'   => 'Sugerido',
            'aprobado'   => 'Aprobado',
            'completado' => 'Completado',
            'cancelado'  => 'Cancelado',
            default      => ucfirst($traslado->estado),
        };

        $origenLat  = $origen?->latitud;
        $origenLng  = $origen?->longitud;
        $destinoLat = $destino?->latitud;
        $destinoLng = $destino?->longitud;
        $tieneOrigen  = $origenLat && $origenLng;
        $tieneDestino = $destinoLat && $destinoLng;
        $tieneMapa    = $tieneOrigen && $tieneDestino;

        $transLat = $transportista?->latitud;
        $transLng = $transportista?->longitud;
        $tieneGps = $transLat && $transLng;

        $mapsKey = config('app.google.maps_key');
    @endphp

    <div class="space-y-4">

        {{-- KPIs --}}
        <div class="flex gap-4">

            <div class="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-5 h-24">
                <div class="flex items-center justify-between h-full">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Estado</p>
                        <p class="text-lg font-bold mt-0.5 text-{{ $estadoColor }}-600 dark:text-{{ $estadoColor }}-400">{{ $estadoLabel }}</p>
                    </div>
                    <div class="p-2.5 rounded-xl bg-{{ $estadoColor }}-100 dark:bg-{{ $estadoColor }}-900/30">
                        <x-heroicon-m-arrow-path class="w-6 h-6 text-{{ $estadoColor }}-600 dark:text-{{ $estadoColor }}-400"/>
                    </div>
                </div>
            </div>

            <div class="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-5 h-24">
                <div class="flex items-center justify-between h-full">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Distancia</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-0.5">
                            {{ $distanciaKm !== null ? number_format($distanciaKm, 1) . ' km' : '—' }}
                        </p>
                    </div>
                    <div class="p-2.5 rounded-xl bg-primary-100 dark:bg-primary-900/30">
                        <x-heroicon-m-map-pin class="w-6 h-6 text-primary-600 dark:text-primary-400"/>
                    </div>
                </div>
            </div>

            <div class="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-5 h-24">
                <div class="flex items-center justify-between h-full">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Costo Est.</p>
                        <p class="text-2xl font-bold text-success-600 dark:text-success-400 mt-0.5">
                            {{ $costo !== null ? '$' . number_format($costo, 2) : '—' }}
                        </p>
                        @if($distanciaKm)
                            <p class="text-xs text-gray-400 mt-0.5">$0.50/km</p>
                        @endif
                    </div>
                    <div class="p-2.5 rounded-xl bg-success-100 dark:bg-success-900/30">
                        <x-heroicon-m-currency-dollar class="w-6 h-6 text-success-600 dark:text-success-400"/>
                    </div>
                </div>
            </div>

            <div class="flex-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-5 h-24">
                <div class="flex items-center justify-between h-full">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Productos</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-0.5">{{ $traslado->items->count() }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">items</p>
                    </div>
                    <div class="p-2.5 rounded-xl bg-warning-100 dark:bg-warning-900/30">
                        <x-heroicon-m-cube class="w-6 h-6 text-warning-600 dark:text-warning-400"/>
                    </div>
                </div>
            </div>

        </div>

        {{-- Mapa + Conductor --}}
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">

            <x-filament::section class="lg:col-span-2">
                <x-slot name="heading">
                    <span class="flex items-center gap-2">
                        <x-heroicon-m-map class="w-4 h-4 text-primary-500"/>
                        Ruta del Traslado
                        @if(!$tieneMapa)
                            <span class="text-xs text-warning-500 font-normal">Sin coordenadas configuradas</span>
                        @endif
                    </span>
                </x-slot>
                @if($tieneMapa)
                    <div id="mapa-traslado" style="height:340px;width:100%;border-radius:8px;position:relative;z-index:1;"></div>
                @else
                    <div class="text-center py-14 text-gray-400">
                        <x-heroicon-m-map class="w-10 h-10 mx-auto mb-2 opacity-30"/>
                        <p class="text-sm">Configura coordenadas en los almacenes para ver el mapa.</p>
                    </div>
                @endif
            </x-filament::section>

            <div class="flex flex-col gap-4">

                <x-filament::section>
                    <x-slot name="heading">
                        <span class="flex items-center gap-2">
                            <x-heroicon-m-user-circle class="w-4 h-4 text-primary-500"/>
                            Conductor
                        </span>
                    </x-slot>
                    @if($conductor)
                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <span @class([
                                    'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold',
                                    'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400' => $transportista->estado === 'disponible',
                                    'bg-warning-100 text-warning-700 dark:bg-warning-900/30 dark:text-warning-400' => $transportista->estado === 'en_ruta',
                                    'bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-400'     => $transportista->estado === 'mantenimiento',
                                    'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300'               => !in_array($transportista->estado, ['disponible','en_ruta','mantenimiento']),
                                ])>{{ ucfirst($transportista->estado) }}</span>
                            </div>
                            <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $conductor->name }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $conductor->email }}</p>
                            <div class="border-t border-gray-100 dark:border-gray-700 pt-2 grid grid-cols-2 gap-1 text-xs">
                                <span class="font-medium text-gray-500 dark:text-gray-400">Vehiculo</span>
                                <span class="text-gray-700 dark:text-gray-300">{{ ucfirst($transportista->vehiculo_tipo ?? '—') }}</span>
                                <span class="font-medium text-gray-500 dark:text-gray-400">Placa</span>
                                <span class="font-mono text-gray-700 dark:text-gray-300">{{ $transportista->vehiculo_placa ?? '—' }}</span>
                                <span class="font-medium text-gray-500 dark:text-gray-400">Modelo</span>
                                <span class="text-gray-700 dark:text-gray-300">{{ $transportista->vehiculo_modelo ?? '—' }}</span>
                                <span class="font-medium text-gray-500 dark:text-gray-400">Cap. kg</span>
                                <span class="text-gray-700 dark:text-gray-300">{{ $transportista->capacidad_kg ? number_format($transportista->capacidad_kg, 0) . ' kg' : '—' }}</span>
                                <span class="font-medium text-gray-500 dark:text-gray-400">GPS</span>
                                <span @class([
                                    'font-semibold',
                                    'text-success-600 dark:text-success-400' => $transportista->tiene_gps,
                                    'text-gray-400'                          => !$transportista->tiene_gps,
                                ])>{{ $transportista->tiene_gps ? 'Activo' : 'Sin GPS' }}</span>
                                <span class="font-medium text-gray-500 dark:text-gray-400">Refrig.</span>
                                <span class="text-gray-700 dark:text-gray-300">{{ $transportista->tiene_refrigeracion ? 'Sí' : '—' }}</span>
                            </div>
                            @if($tieneGps)
                                <p class="text-xs text-gray-400 pt-1">Última pos: {{ $transLat }}, {{ $transLng }}</p>
                            @endif
                        </div>
                    @else
                        <div class="text-center py-6 text-gray-400">
                            <x-heroicon-m-user-circle class="w-8 h-8 mx-auto mb-2 opacity-30"/>
                            <p class="text-sm">Sin conductor asignado</p>
                        </div>
                    @endif
                </x-filament::section>

                <x-filament::section>
                    <x-slot name="heading">
                        <span class="flex items-center gap-2">
                            <x-heroicon-m-building-storefront class="w-4 h-4 text-primary-500"/>
                            Sucursales
                        </span>
                    </x-slot>
                    <div class="space-y-3">
                        <div class="flex gap-3 items-start">
                            <span class="flex-shrink-0 w-6 h-6 rounded-full bg-success-500 flex items-center justify-center text-white text-xs font-bold">A</span>
                            <div>
                                <p class="text-xs text-gray-400 uppercase tracking-wide">Origen</p>
                                <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $origen?->nombre ?? '—' }}</p>
                                @if($origen?->direccion)
                                    <p class="text-xs text-gray-400">{{ $origen->direccion }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="ml-3 border-l-2 border-dashed border-gray-200 dark:border-gray-600 h-4"></div>
                        <div class="flex gap-3 items-start">
                            <span class="flex-shrink-0 w-6 h-6 rounded-full bg-danger-500 flex items-center justify-center text-white text-xs font-bold">B</span>
                            <div>
                                <p class="text-xs text-gray-400 uppercase tracking-wide">Destino</p>
                                <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $destino?->nombre ?? '—' }}</p>
                                @if($destino?->direccion)
                                    <p class="text-xs text-gray-400">{{ $destino->direccion }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </x-filament::section>

            </div>
        </div>

        {{-- Timeline --}}
        <x-filament::section>
            <x-slot name="heading">
                <span class="flex items-center gap-2">
                    <x-heroicon-m-clock class="w-4 h-4 text-primary-500"/>
                    Línea de Tiempo
                </span>
            </x-slot>
            @php
                $steps = [
                    ['label' => 'Creado',    'date' => $traslado->created_at,       'done' => true,                                                             'icon' => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z'],
                    ['label' => 'Sugerido',  'date' => null,                        'done' => in_array($traslado->estado, ['sugerido','aprobado','completado']), 'icon' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['label' => 'Aprobado',  'date' => $traslado->fecha_aprobacion, 'done' => in_array($traslado->estado, ['aprobado','completado']),            'icon' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['label' => 'Completado','date' => $traslado->fecha_completado, 'done' => $traslado->estado === 'completado',                                'icon' => 'M6.633 10.5c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 012.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 00.322-1.672V3a.75.75 0 01.75-.75A2.25 2.25 0 0116.5 4.5c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 01-2.649 7.521c-.388.482-.987.729-1.605.729H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 00-1.423-.23H5.904M14.25 9h2.25M5.904 18.75c.083.205.173.405.27.602.197.4-.078.898-.523.898h-.908c-.889 0-1.713-.518-1.972-1.368a12 12 0 01-.521-3.507c0-1.553.295-3.036.831-4.398C3.387 10.203 4.167 9.75 5 9.75h1.053c.472 0 .745.556.5.96a8.958 8.958 0 00-1.302 4.665c0 1.194.232 2.333.654 3.375z'],
                ];
            @endphp
            <ol class="flex items-center">
                @foreach($steps as $i => $step)
                    <li class="flex-1 relative">
                        <div class="flex flex-col items-center">
                            <div @class([
                                'w-8 h-8 rounded-full flex items-center justify-center z-10',
                                'bg-primary-500 text-white'                  => $step['done'],
                                'bg-gray-200 dark:bg-gray-700 text-gray-400' => !$step['done'],
                            ])>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $step['icon'] }}"/>
                                </svg>
                            </div>
                            <p @class([
                                'text-xs mt-1 font-medium',
                                'text-primary-600 dark:text-primary-400' => $step['done'],
                                'text-gray-400'                          => !$step['done'],
                            ])>{{ $step['label'] }}</p>
                            @if($step['date'])
                                <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($step['date'])->format('d/m/Y H:i') }}</p>
                            @endif
                        </div>
                        @if(!$loop->last)
                            <div @class([
                                'absolute top-4 left-1/2 w-full h-0.5',
                                'bg-primary-400'               => $step['done'],
                                'bg-gray-200 dark:bg-gray-700' => !$step['done'],
                            ])></div>
                        @endif
                    </li>
                @endforeach
            </ol>
        </x-filament::section>

        {{-- Productos --}}
        <x-filament::section>
            <x-slot name="heading">
                <span class="flex items-center gap-2">
                    <x-heroicon-m-list-bullet class="w-4 h-4 text-primary-500"/>
                    Productos a Trasladar
                    <span class="bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400 text-xs font-bold px-2 py-0.5 rounded-full">
                        {{ $traslado->items->count() }}
                    </span>
                </span>
            </x-slot>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="text-left py-2.5 px-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Producto</th>
                        <th class="text-right py-2.5 px-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Cant. Sugerida</th>
                        <th class="text-right py-2.5 px-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Cant. Real</th>
                        <th class="text-left py-2.5 px-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Lote</th>
                        <th class="text-left py-2.5 px-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Vencimiento</th>
                        <th class="text-left py-2.5 px-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Notas</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($traslado->items as $item)
                        <tr class="hover:bg-gray-100/60 dark:hover:bg-white/5 transition-colors">
                            <td class="py-3 px-3 font-medium text-gray-800 dark:text-gray-200">{{ $item->producto?->nombre ?? '—' }}</td>
                            <td class="py-3 px-3 text-right font-semibold text-primary-600 dark:text-primary-400 tabular-nums">{{ number_format($item->cantidad_sugerida, 2) }}</td>
                            <td class="py-3 px-3 text-right tabular-nums">
                                @if($item->cantidad_real !== null)
                                    <span @class([
                                        'font-semibold',
                                        'text-success-600 dark:text-success-400' => $item->cantidad_real >= $item->cantidad_sugerida,
                                        'text-warning-600 dark:text-warning-400' => $item->cantidad_real < $item->cantidad_sugerida,
                                    ])>{{ number_format($item->cantidad_real, 2) }}</span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 font-mono text-xs text-gray-500 dark:text-gray-400">{{ $item->lote ?? '—' }}</td>
                            <td class="py-3 px-3 text-xs text-gray-500 dark:text-gray-400">{{ $item->fecha_vencimiento?->format('d/m/Y') ?? '—' }}</td>
                            <td class="py-3 px-3 text-xs text-gray-500 dark:text-gray-400">{{ $item->notas ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-gray-400">
                                <x-heroicon-m-cube class="w-8 h-8 mx-auto mb-2 opacity-30"/>
                                <p class="text-sm">Sin productos</p>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        {{-- Desglose costos --}}
        @if($distanciaKm)
            <x-filament::section>
                <x-slot name="heading">
                    <span class="flex items-center gap-2">
                        <x-heroicon-m-calculator class="w-4 h-4 text-primary-500"/>
                        Desglose de Costos
                    </span>
                </x-slot>
                <div class="grid grid-cols-3 gap-4">
                    <div class="text-center p-3 rounded-lg bg-gray-50 dark:bg-gray-700/40">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-1">Distancia</p>
                        <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ number_format($distanciaKm, 1) }} km</p>
                    </div>
                    <div class="text-center p-3 rounded-lg bg-gray-50 dark:bg-gray-700/40">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-1">Tarifa/km</p>
                        <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">$0.50</p>
                    </div>
                    <div class="text-center p-3 rounded-lg bg-success-50 dark:bg-success-900/20">
                        <p class="text-xs font-semibold uppercase tracking-wide text-success-600 dark:text-success-400 mb-1">Costo Total Est.</p>
                        <p class="text-2xl font-bold text-success-700 dark:text-success-300">${{ number_format($costo, 2) }}</p>
                    </div>
                </div>
            </x-filament::section>
        @endif

    </div>

    {{-- Google Maps --}}
    @if($tieneMapa)
        @push('scripts')
            <script>
                (function () {
                    var MAPS_KEY = "{{ $mapsKey }}";
                    var CFG = {
                        origen:         { lat: {{ $origenLat }},  lng: {{ $origenLng }}  },
                        destino:        { lat: {{ $destinoLat }}, lng: {{ $destinoLng }} },
                        origenNombre:   "{{ addslashes($origen->nombre) }}",
                        destinoNombre:  "{{ addslashes($destino->nombre) }}",
                        origenDir:      "{{ addslashes($origen->direccion ?? '') }}",
                        destinoDir:     "{{ addslashes($destino->direccion ?? '') }}",
                        @if($tieneGps)
                        gps:             { lat: {{ $transLat }}, lng: {{ $transLng }} },
                        conductorNombre: "{{ addslashes($conductor?->name ?? 'Conductor') }}",
                        conductorPlaca:  "{{ addslashes($transportista->vehiculo_placa ?? '') }}",
                        @else
                        gps: null,
                        @endif
                    };

                    function svgCircleIcon(letter, bg, size) {
                        size = size || 38;
                        var h = size / 2;
                        var svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' + size + '" height="' + size + '">'
                            + '<circle cx="' + h + '" cy="' + h + '" r="' + (h - 2) + '" fill="' + bg + '" stroke="#fff" stroke-width="2.5"/>'
                            + '<text x="' + h + '" y="' + (h + 5) + '" text-anchor="middle" fill="#fff" font-size="'
                            + Math.round(size * 0.39) + '" font-weight="700" font-family="system-ui,sans-serif">' + letter + '</text>'
                            + '</svg>';
                        return {
                            url:        'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(svg),
                            scaledSize: new google.maps.Size(size, size),
                            anchor:     new google.maps.Point(h, h),
                        };
                    }

                    function svgTruckIcon() {
                        var svg = '<svg xmlns="http://www.w3.org/2000/svg" width="52" height="30">'
                            + '<rect rx="6" ry="6" width="52" height="30" fill="#f59e0b" stroke="#fff" stroke-width="1.5"/>'
                            + '<text x="26" y="20" text-anchor="middle" fill="#fff" font-size="12" font-weight="700" font-family="system-ui,sans-serif">GPS</text>'
                            + '</svg>';
                        return {
                            url:        'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(svg),
                            scaledSize: new google.maps.Size(52, 30),
                            anchor:     new google.maps.Point(26, 15),
                        };
                    }

                    function infoHtml(nombre, sub) {
                        return '<div style="font-family:system-ui,sans-serif;font-size:13px;max-width:190px">'
                            + '<b>' + nombre + '</b>'
                            + (sub ? '<br><span style="color:#6b7280;font-size:11px">' + sub + '</span>' : '')
                            + '</div>';
                    }

                    function initMapa() {
                        var el = document.getElementById('mapa-traslado');
                        if (!el || el._gmaps_init) return;
                        el._gmaps_init = true;

                        var map = new google.maps.Map(el, {
                            zoom:              12,
                            center:            CFG.origen,
                            mapTypeControl:    false,
                            fullscreenControl: true,
                            streetViewControl: false,
                            zoomControlOptions: { position: google.maps.ControlPosition.RIGHT_CENTER },
                            styles: [
                                { featureType: 'poi',     stylers: [{ visibility: 'off' }] },
                                { featureType: 'transit', stylers: [{ visibility: 'off' }] },
                            ],
                        });

                        var mA    = new google.maps.Marker({ position: CFG.origen,  map: map, title: CFG.origenNombre,  icon: svgCircleIcon('A', '#22c55e', 38), zIndex: 10 });
                        var infoA = new google.maps.InfoWindow({ content: infoHtml(CFG.origenNombre, CFG.origenDir) });
                        mA.addListener('click', function () { infoA.open(map, mA); });
                        infoA.open(map, mA);

                        var mB    = new google.maps.Marker({ position: CFG.destino, map: map, title: CFG.destinoNombre, icon: svgCircleIcon('B', '#ef4444', 38), zIndex: 10 });
                        var infoB = new google.maps.InfoWindow({ content: infoHtml(CFG.destinoNombre, CFG.destinoDir) });
                        mB.addListener('click', function () { infoB.open(map, mB); });
                        infoB.open(map, mB);

                        if (CFG.gps) {
                            var mT    = new google.maps.Marker({ position: CFG.gps, map: map, title: CFG.conductorNombre, icon: svgTruckIcon(), zIndex: 20 });
                            var infoT = new google.maps.InfoWindow({ content: infoHtml(CFG.conductorNombre, CFG.conductorPlaca) });
                            mT.addListener('click', function () { infoT.open(map, mT); });
                        }

                        var svc      = new google.maps.DirectionsService();
                        var renderer = new google.maps.DirectionsRenderer({
                            map:             map,
                            suppressMarkers: true,
                            polylineOptions: { strokeColor: '#3b82f6', strokeWeight: 5, strokeOpacity: 0.88 },
                        });

                        svc.route({
                            origin:      CFG.origen,
                            destination: CFG.destino,
                            travelMode:  google.maps.TravelMode.DRIVING,
                        }, function (result, status) {
                            if (status === google.maps.DirectionsStatus.OK) {
                                renderer.setDirections(result);
                                var b = new google.maps.LatLngBounds();
                                result.routes[0].overview_path.forEach(function (p) { b.extend(p); });
                                map.fitBounds(b, { top: 50, right: 50, bottom: 50, left: 50 });
                            } else {
                                new google.maps.Polyline({
                                    path: [CFG.origen, CFG.destino], map: map,
                                    strokeColor: '#3b82f6', strokeWeight: 4, strokeOpacity: 0.75,
                                    icons: [{ icon: { path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW, scale: 3 }, offset: '50%' }],
                                });
                                var fb = new google.maps.LatLngBounds();
                                fb.extend(CFG.origen); fb.extend(CFG.destino);
                                map.fitBounds(fb, { top: 60, right: 60, bottom: 60, left: 60 });
                                console.warn('[Mapa] Directions fallo (' + status + ').');
                            }
                        });
                    }

                    window.__initMapaTraslado = function () { initMapa(); };

                    document.addEventListener('livewire:navigated', function () {
                        var el = document.getElementById('mapa-traslado');
                        if (!el) return;
                        el._gmaps_init = false;
                        if (typeof google !== 'undefined' && google.maps && google.maps.Map) {
                            initMapa();
                        }
                    });

                    if (!window.__gmapsSdkLoaded) {
                        window.__gmapsSdkLoaded = true;
                        var s = document.createElement('script');
                        s.src = 'https://maps.googleapis.com/maps/api/js?key=' + MAPS_KEY + '&v=weekly&callback=__initMapaTraslado';
                        s.async = true;
                        s.defer = true;
                        document.head.appendChild(s);
                    } else {
                        if (document.readyState === 'loading') {
                            document.addEventListener('DOMContentLoaded', initMapa);
                        } else {
                            setTimeout(initMapa, 50);
                        }
                    }

                })();
            </script>
        @endpush
    @endif

</x-filament-panels::page>