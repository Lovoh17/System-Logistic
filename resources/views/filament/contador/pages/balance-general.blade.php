<x-filament-panels::page>
<div class="space-y-6">

    {{-- Encabezado --}}
    <div class="fi-section rounded-xl p-5 flex justify-between items-center
                bg-gradient-to-r from-emerald-700 to-teal-600 text-white shadow-lg">
        <div class="flex items-center gap-3">
            <div class="bg-white/20 p-3 rounded-xl">
                <x-heroicon-o-scale class="w-7 h-7"/>
            </div>
            <div>
                <p class="text-sm opacity-80">Estados Financieros</p>
                <p class="text-xl font-bold">Balance General</p>
                @if($this->hasta)
                <p class="text-sm opacity-70">Al {{ \Carbon\Carbon::parse($this->hasta)->format('d/m/Y') }}</p>
                @endif
            </div>
        </div>
        <div class="text-right">
            @if($this->balanceado)
            <span class="px-3 py-1 bg-green-400/30 border border-green-300 rounded-lg text-sm font-bold">
                BALANCEADO
            </span>
            @else
            <span class="px-3 py-1 bg-red-400/30 border border-red-300 rounded-lg text-sm font-bold">
                DESBALANCEADO
            </span>
            @endif
        </div>
    </div>

    {{-- Filtro fecha --}}
    <x-filament::section>
        <x-slot name="heading">
            <span class="flex items-center gap-2 text-sm font-semibold">
                <x-heroicon-m-calendar-days class="w-4 h-4 text-emerald-500"/>
                Período
            </span>
        </x-slot>
        <form wire:submit="calcular">
            {{ $this->form }}
            <div class="mt-4">
                <x-filament::button type="submit" icon="heroicon-m-arrow-path" color="primary">
                    Recalcular
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>

    {{-- Resumen KPI --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-filament::section class="text-center">
            <div class="text-3xl font-bold text-blue-700">${{ number_format($this->totalActivos, 2) }}</div>
            <div class="text-sm text-gray-500 mt-1 font-medium">Total Activos</div>
        </x-filament::section>
        <x-filament::section class="text-center">
            <div class="text-3xl font-bold text-orange-600">${{ number_format($this->totalPasivos, 2) }}</div>
            <div class="text-sm text-gray-500 mt-1 font-medium">Total Pasivos</div>
        </x-filament::section>
        <x-filament::section class="text-center">
            <div class="text-3xl font-bold text-emerald-700">${{ number_format($this->totalCapital, 2) }}</div>
            <div class="text-sm text-gray-500 mt-1 font-medium">Capital</div>
        </x-filament::section>
    </div>

    {{-- Balance en dos columnas --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- ACTIVOS --}}
        <x-filament::section>
            <x-slot name="heading">
                <span class="flex items-center gap-2 font-bold text-blue-700">
                    <x-heroicon-m-building-office-2 class="w-5 h-5"/>
                    ACTIVOS
                </span>
            </x-slot>
            @if(count($this->activos) > 0)
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-blue-100 text-blue-600 text-xs uppercase">
                        <th class="py-2 text-left">Código</th>
                        <th class="py-2 text-left">Cuenta</th>
                        <th class="py-2 text-right">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($this->activos as $item)
                    <tr class="border-b border-gray-50 hover:bg-blue-50/50">
                        <td class="py-2">
                            <span class="text-xs font-mono text-gray-500">{{ $item['codigo'] }}</span>
                        </td>
                        <td class="py-2 text-gray-700">{{ $item['nombre'] }}</td>
                        <td class="py-2 text-right font-medium text-blue-700">${{ number_format($item['saldo'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-blue-300 bg-blue-50">
                        <td class="py-3 font-bold text-blue-800" colspan="2">TOTAL ACTIVOS</td>
                        <td class="py-3 text-right font-bold text-blue-800 text-lg">${{ number_format($this->totalActivos, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
            @else
            <p class="text-center text-gray-400 py-6">Sin movimientos de activos en el período</p>
            @endif
        </x-filament::section>

        {{-- PASIVOS + CAPITAL --}}
        <div class="space-y-4">
            <x-filament::section>
                <x-slot name="heading">
                    <span class="flex items-center gap-2 font-bold text-orange-700">
                        <x-heroicon-m-banknotes class="w-5 h-5"/>
                        PASIVOS
                    </span>
                </x-slot>
                @if(count($this->pasivos) > 0)
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-orange-100 text-orange-600 text-xs uppercase">
                            <th class="py-2 text-left">Código</th>
                            <th class="py-2 text-left">Cuenta</th>
                            <th class="py-2 text-right">Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($this->pasivos as $item)
                        <tr class="border-b border-gray-50 hover:bg-orange-50/50">
                            <td class="py-2 text-xs font-mono text-gray-500">{{ $item['codigo'] }}</td>
                            <td class="py-2 text-gray-700">{{ $item['nombre'] }}</td>
                            <td class="py-2 text-right font-medium text-orange-700">${{ number_format($item['saldo'], 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-orange-300 bg-orange-50">
                            <td class="py-2 font-bold text-orange-800" colspan="2">TOTAL PASIVOS</td>
                            <td class="py-2 text-right font-bold text-orange-800">${{ number_format($this->totalPasivos, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
                @else
                <p class="text-center text-gray-400 py-4">Sin movimientos de pasivos</p>
                @endif
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">
                    <span class="flex items-center gap-2 font-bold text-emerald-700">
                        <x-heroicon-m-arrow-trending-up class="w-5 h-5"/>
                        CAPITAL
                    </span>
                </x-slot>
                @if(count($this->capital) > 0)
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-emerald-100 text-emerald-600 text-xs uppercase">
                            <th class="py-2 text-left">Código</th>
                            <th class="py-2 text-left">Cuenta</th>
                            <th class="py-2 text-right">Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($this->capital as $item)
                        <tr class="border-b border-gray-50 hover:bg-emerald-50/50">
                            <td class="py-2 text-xs font-mono text-gray-500">{{ $item['codigo'] }}</td>
                            <td class="py-2 text-gray-700">{{ $item['nombre'] }}</td>
                            <td class="py-2 text-right font-medium text-emerald-700">${{ number_format($item['saldo'], 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-emerald-300 bg-emerald-50">
                            <td class="py-2 font-bold text-emerald-800" colspan="2">TOTAL CAPITAL</td>
                            <td class="py-2 text-right font-bold text-emerald-800">${{ number_format($this->totalCapital, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
                @else
                <p class="text-center text-gray-400 py-4">Sin movimientos de capital</p>
                @endif
            </x-filament::section>

            {{-- Subtotal Pasivo + Capital --}}
            <div class="bg-gray-800 rounded-xl p-4 text-white flex justify-between items-center">
                <span class="font-bold text-sm">TOTAL PASIVOS + CAPITAL</span>
                <span class="font-bold text-xl">${{ number_format($this->totalPasivoCapital, 2) }}</span>
            </div>
        </div>
    </div>

    {{-- Verificación de cuadre --}}
    <div class="rounded-xl p-5 border-2 {{ $this->balanceado ? 'bg-green-50 border-green-400' : 'bg-red-50 border-red-400' }}">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                @if($this->balanceado)
                    <x-heroicon-o-check-circle class="w-8 h-8 text-green-600"/>
                    <div>
                        <p class="font-bold text-green-700">Balance Cuadra Correctamente</p>
                        <p class="text-sm text-green-600">Activos (${{ number_format($this->totalActivos, 2) }}) = Pasivos + Capital (${{ number_format($this->totalPasivoCapital, 2) }})</p>
                    </div>
                @else
                    <x-heroicon-o-x-circle class="w-8 h-8 text-red-600"/>
                    <div>
                        <p class="font-bold text-red-700">Balance No Cuadra</p>
                        <p class="text-sm text-red-600">
                            Diferencia: ${{ number_format(abs($this->totalActivos - $this->totalPasivoCapital), 2) }}
                            — Verifique los asientos contables.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>
</x-filament-panels::page>
