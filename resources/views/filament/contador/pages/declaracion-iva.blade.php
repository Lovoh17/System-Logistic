<x-filament-panels::page>
<div class="space-y-6">

    {{-- Encabezado --}}
    <div class="fi-section rounded-xl p-5 flex justify-between items-center
                bg-gradient-to-r from-violet-700 to-purple-500 text-white shadow-lg">
        <div class="flex items-center gap-3">
            <div class="bg-white/20 p-3 rounded-xl">
                <x-heroicon-o-receipt-percent class="w-7 h-7"/>
            </div>
            <div>
                <p class="text-sm opacity-80">Reportes Tributarios</p>
                <p class="text-xl font-bold">Declaración de IVA Mensual</p>
                @if($this->periodo)
                <p class="text-sm opacity-70">Período: {{ $this->periodo }}</p>
                @endif
            </div>
        </div>
        <div class="text-right text-sm opacity-80">
            <p>Tasa IVA: 13%</p>
            <p>Legislación: Ley del IVA El Salvador</p>
        </div>
    </div>

    {{-- Filtro período --}}
    <x-filament::section>
        <x-slot name="heading">
            <span class="flex items-center gap-2 text-sm font-semibold">
                <x-heroicon-m-calendar-days class="w-4 h-4 text-violet-500"/>
                Período de Declaración
            </span>
        </x-slot>
        <form wire:submit="calcular">
            {{ $this->form }}
            <div class="mt-4">
                <x-filament::button type="submit" icon="heroicon-m-arrow-path" color="primary">
                    Calcular
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>

    {{-- Resumen principal --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-filament::section class="text-center border-2 border-orange-200">
            <div class="text-3xl font-bold text-orange-600">${{ number_format($this->debitoFiscal, 2) }}</div>
            <div class="text-sm font-semibold text-gray-600 mt-1">Débito Fiscal</div>
            <div class="text-xs text-gray-400 mt-1">IVA cobrado en ventas (cuenta 2.1.02)</div>
        </x-filament::section>

        <x-filament::section class="text-center border-2 border-blue-200">
            <div class="text-3xl font-bold text-blue-600">${{ number_format($this->creditoFiscal, 2) }}</div>
            <div class="text-sm font-semibold text-gray-600 mt-1">Crédito Fiscal</div>
            <div class="text-xs text-gray-400 mt-1">IVA pagado en compras (cuenta 1.1.04)</div>
        </x-filament::section>

        @if($this->ivaNetoPagar > 0)
        <x-filament::section class="text-center border-2 border-red-300 bg-red-50">
            <div class="text-3xl font-bold text-red-600">${{ number_format($this->ivaNetoPagar, 2) }}</div>
            <div class="text-sm font-semibold text-red-700 mt-1">IVA a Pagar</div>
            <div class="text-xs text-red-400 mt-1">A declarar y pagar a Hacienda</div>
        </x-filament::section>
        @else
        <x-filament::section class="text-center border-2 border-green-300 bg-green-50">
            <div class="text-3xl font-bold text-green-600">${{ number_format($this->ivaFavorContribuyente, 2) }}</div>
            <div class="text-sm font-semibold text-green-700 mt-1">Remanente a Favor</div>
            <div class="text-xs text-green-400 mt-1">Crédito fiscal pendiente de aplicar</div>
        </x-filament::section>
        @endif
    </div>

    {{-- Detalle Débito Fiscal --}}
    <x-filament::section>
        <x-slot name="heading">
            <span class="flex items-center gap-2 text-sm font-semibold text-orange-700">
                <x-heroicon-m-arrow-up-circle class="w-4 h-4"/>
                Detalle de Débito Fiscal (Ventas)
            </span>
        </x-slot>
        @if(count($this->detalleDebito) > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-orange-50 border-b border-orange-200 text-orange-600 text-xs uppercase">
                        <th class="px-4 py-2 text-left">Fecha</th>
                        <th class="px-4 py-2 text-left">N° Asiento</th>
                        <th class="px-4 py-2 text-left">Tipo Documento</th>
                        <th class="px-4 py-2 text-left">N° Documento</th>
                        <th class="px-4 py-2 text-right">IVA Débito</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($this->detalleDebito as $row)
                    <tr class="border-b border-gray-100 hover:bg-orange-50/30">
                        <td class="px-4 py-2 text-gray-600">
                            {{ \Carbon\Carbon::parse($row->fecha)->format('d/m/Y') }}
                        </td>
                        <td class="px-4 py-2">
                            <span class="px-2 py-0.5 bg-orange-100 text-orange-700 rounded text-xs font-mono">
                                {{ $row->numero }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-gray-600">
                            {{ match($row->tipo_documento) {
                                'factura_cf' => 'Factura CF',
                                'ccf'        => 'CCF',
                                default      => $row->tipo_documento,
                            } }}
                        </td>
                        <td class="px-4 py-2 text-gray-600">{{ $row->numero_documento ?? '—' }}</td>
                        <td class="px-4 py-2 text-right font-medium text-orange-700">${{ number_format($row->monto, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-orange-100 border-t-2 border-orange-300 font-bold text-orange-800">
                        <td class="px-4 py-3" colspan="4">TOTAL DÉBITO FISCAL</td>
                        <td class="px-4 py-3 text-right">${{ number_format($this->debitoFiscal, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @else
        <p class="text-center text-gray-400 py-6">Sin débito fiscal en este período.</p>
        @endif
    </x-filament::section>

    {{-- Detalle Crédito Fiscal --}}
    <x-filament::section>
        <x-slot name="heading">
            <span class="flex items-center gap-2 text-sm font-semibold text-blue-700">
                <x-heroicon-m-arrow-down-circle class="w-4 h-4"/>
                Detalle de Crédito Fiscal (Compras)
            </span>
        </x-slot>
        @if(count($this->detalleCredito) > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-blue-50 border-b border-blue-200 text-blue-600 text-xs uppercase">
                        <th class="px-4 py-2 text-left">Fecha</th>
                        <th class="px-4 py-2 text-left">N° Asiento</th>
                        <th class="px-4 py-2 text-left">Tipo Documento</th>
                        <th class="px-4 py-2 text-left">N° Documento</th>
                        <th class="px-4 py-2 text-right">IVA Crédito</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($this->detalleCredito as $row)
                    <tr class="border-b border-gray-100 hover:bg-blue-50/30">
                        <td class="px-4 py-2 text-gray-600">
                            {{ \Carbon\Carbon::parse($row->fecha)->format('d/m/Y') }}
                        </td>
                        <td class="px-4 py-2">
                            <span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded text-xs font-mono">
                                {{ $row->numero }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-gray-600">
                            {{ match($row->tipo_documento) {
                                'factura_cf' => 'Factura CF',
                                'ccf'        => 'CCF',
                                default      => $row->tipo_documento,
                            } }}
                        </td>
                        <td class="px-4 py-2 text-gray-600">{{ $row->numero_documento ?? '—' }}</td>
                        <td class="px-4 py-2 text-right font-medium text-blue-700">${{ number_format($row->monto, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-blue-100 border-t-2 border-blue-300 font-bold text-blue-800">
                        <td class="px-4 py-3" colspan="4">TOTAL CRÉDITO FISCAL</td>
                        <td class="px-4 py-3 text-right">${{ number_format($this->creditoFiscal, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @else
        <p class="text-center text-gray-400 py-6">Sin crédito fiscal en este período.</p>
        @endif
    </x-filament::section>

    {{-- Resumen de liquidación --}}
    <div class="rounded-xl border-2 {{ $this->ivaNetoPagar > 0 ? 'border-red-300 bg-red-50' : 'border-green-300 bg-green-50' }} p-5">
        <h3 class="font-bold text-lg {{ $this->ivaNetoPagar > 0 ? 'text-red-800' : 'text-green-800' }} mb-4">
            Liquidación de IVA — {{ $this->periodo }}
        </h3>
        <div class="space-y-2 text-sm">
            <div class="flex justify-between py-2 border-b border-gray-200">
                <span class="text-gray-600">Débito Fiscal (ventas gravadas × 13%)</span>
                <span class="font-medium">${{ number_format($this->debitoFiscal, 2) }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-200">
                <span class="text-gray-600">Crédito Fiscal (compras gravadas × 13%)</span>
                <span class="font-medium">— ${{ number_format($this->creditoFiscal, 2) }}</span>
            </div>
            <div class="flex justify-between py-3 font-bold text-base {{ $this->ivaNetoPagar > 0 ? 'text-red-700' : 'text-green-700' }}">
                <span>{{ $this->ivaNetoPagar > 0 ? 'IVA a Declarar y Pagar' : 'Remanente a Favor del Contribuyente' }}</span>
                <span>${{ number_format($this->ivaNetoPagar > 0 ? $this->ivaNetoPagar : $this->ivaFavorContribuyente, 2) }}</span>
            </div>
        </div>
    </div>

</div>
</x-filament-panels::page>
