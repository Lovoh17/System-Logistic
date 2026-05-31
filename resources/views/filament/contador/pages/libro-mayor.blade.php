<x-filament-panels::page>
<div class="space-y-6">

    {{-- Encabezado --}}
    <div class="fi-section rounded-xl p-5 flex justify-between items-center
                bg-gradient-to-r from-teal-600 to-emerald-500 text-white shadow-lg">
        <div class="flex items-center gap-3">
            <div class="bg-white/20 p-3 rounded-xl">
                <x-heroicon-o-table-cells class="w-7 h-7"/>
            </div>
            <div>
                <p class="text-sm opacity-80">Contabilidad</p>
                <p class="text-xl font-bold">Libro Mayor</p>
            </div>
        </div>
        <div class="text-right text-sm opacity-80">
            <p>Generado: {{ now()->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    {{-- Formulario de consulta --}}
    <x-filament::section>
        <x-slot name="heading">
            <span class="flex items-center gap-2 text-sm font-semibold">
                <x-heroicon-m-magnifying-glass class="w-4 h-4 text-teal-500"/>
                Parámetros de Consulta
            </span>
        </x-slot>
        <form wire:submit="consultar">
            {{ $this->form }}
            <div class="mt-4">
                <x-filament::button type="submit" icon="heroicon-m-magnifying-glass" color="primary">
                    Consultar
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>

    @if($this->consultado && $this->cuenta)
    {{-- Info de la cuenta --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <x-filament::section class="md:col-span-2 text-center">
            <div class="text-lg font-bold text-teal-700">{{ $this->cuenta['codigo'] }} — {{ $this->cuenta['nombre'] }}</div>
            <div class="text-xs text-gray-500 mt-1">{{ $this->cuenta['tipo'] }} · Naturaleza {{ $this->cuenta['naturaleza'] }}</div>
        </x-filament::section>
        <x-filament::section class="text-center">
            <div class="text-2xl font-bold text-gray-700">${{ number_format($this->saldo_inicial, 2) }}</div>
            <div class="text-xs text-gray-500 mt-1">Saldo Inicial del Período</div>
        </x-filament::section>
        <x-filament::section class="text-center">
            <div class="text-2xl font-bold {{ $this->saldo_acumulado >= 0 ? 'text-teal-600' : 'text-red-600' }}">
                ${{ number_format($this->saldo_acumulado, 2) }}
            </div>
            <div class="text-xs text-gray-500 mt-1">Saldo Final del Período</div>
        </x-filament::section>
    </div>

    {{-- Tabla de movimientos --}}
    <x-filament::section>
        <x-slot name="heading">
            <span class="flex items-center gap-2 text-sm font-semibold">
                <x-heroicon-m-list-bullet class="w-4 h-4 text-teal-500"/>
                Movimientos del Período
                <span class="ml-2 px-2 py-0.5 rounded-full bg-teal-100 text-teal-700 text-xs font-medium">
                    {{ count($this->movimientos) }} registros
                </span>
            </span>
        </x-slot>

        @if(count($this->movimientos) > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-gray-600 uppercase text-xs">
                        <th class="px-4 py-2 text-left">Fecha</th>
                        <th class="px-4 py-2 text-left">N° Asiento</th>
                        <th class="px-4 py-2 text-left">Descripción</th>
                        <th class="px-4 py-2 text-right">Débito</th>
                        <th class="px-4 py-2 text-right">Crédito</th>
                        <th class="px-4 py-2 text-right">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Fila saldo inicial --}}
                    <tr class="border-b border-gray-100 bg-gray-50 font-medium text-gray-600">
                        <td class="px-4 py-2" colspan="3">Saldo inicial del período</td>
                        <td class="px-4 py-2 text-right">—</td>
                        <td class="px-4 py-2 text-right">—</td>
                        <td class="px-4 py-2 text-right font-bold">${{ number_format($this->saldo_inicial, 2) }}</td>
                    </tr>
                    @foreach($this->movimientos as $mov)
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-2 text-gray-600">{{ $mov['fecha'] }}</td>
                        <td class="px-4 py-2">
                            <span class="px-2 py-0.5 bg-primary-100 text-primary-700 rounded text-xs font-mono">
                                {{ $mov['numero_asiento'] }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-gray-700 max-w-xs truncate">{{ $mov['descripcion'] }}</td>
                        <td class="px-4 py-2 text-right {{ $mov['debe'] > 0 ? 'text-blue-700 font-medium' : 'text-gray-400' }}">
                            {{ $mov['debe'] > 0 ? '$'.number_format($mov['debe'], 2) : '—' }}
                        </td>
                        <td class="px-4 py-2 text-right {{ $mov['haber'] > 0 ? 'text-green-700 font-medium' : 'text-gray-400' }}">
                            {{ $mov['haber'] > 0 ? '$'.number_format($mov['haber'], 2) : '—' }}
                        </td>
                        <td class="px-4 py-2 text-right font-semibold {{ $mov['saldo'] >= 0 ? 'text-gray-800' : 'text-red-600' }}">
                            ${{ number_format($mov['saldo'], 2) }}
                        </td>
                    </tr>
                    @endforeach
                    {{-- Fila totales --}}
                    <tr class="bg-teal-50 border-t-2 border-teal-300 font-bold text-teal-800">
                        <td class="px-4 py-3" colspan="3">TOTALES DEL PERÍODO</td>
                        <td class="px-4 py-3 text-right">${{ number_format(collect($this->movimientos)->sum('debe'), 2) }}</td>
                        <td class="px-4 py-3 text-right">${{ number_format(collect($this->movimientos)->sum('haber'), 2) }}</td>
                        <td class="px-4 py-3 text-right text-lg">${{ number_format($this->saldo_acumulado, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-12 text-gray-500">
            <x-heroicon-o-inbox class="w-12 h-12 mx-auto mb-3 text-gray-300"/>
            <p>No hay movimientos registrados para esta cuenta en el período seleccionado.</p>
        </div>
        @endif
    </x-filament::section>
    @endif

</div>
</x-filament-panels::page>
