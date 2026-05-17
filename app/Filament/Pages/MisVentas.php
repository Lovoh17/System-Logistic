<?php

namespace App\Filament\Pages;

use App\Models\PedidoVenta;
use App\Models\Almacen;
use Filament\Pages\Page;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Database\Eloquent\Builder;

class MisVentas extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-refund';
    protected static ?string $navigationLabel = 'Mis Ventas';
    protected static ?string $title = 'Ventas de mi Sucursal';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.pages.mis-ventas';

    public function getSucursalActualProperty()
    {
        $user = auth()->user();
        if ($user && $user->almacen_id) {
            return Almacen::find($user->almacen_id);
        }
        return null;
    }

    public function getResumenProperty()
    {
        $user = auth()->user();
        
        $query = PedidoVenta::where('almacen_id', $user->almacen_id)
            ->where('estado', 'entregado');
        
        return [
            'total_ventas' => $query->count(),
            'total_monto' => $query->sum('total'),
            'ventas_hoy' => (clone $query)->whereDate('created_at', today())->count(),
            'monto_hoy' => (clone $query)->whereDate('created_at', today())->sum('total'),
        ];
    }

    public function table(Table $table): Table
    {
        $user = auth()->user();
        
        return $table
            ->query(
                PedidoVenta::query()
                    ->with(['cliente', 'user'])
                    ->where('almacen_id', $user->almacen_id)
                    ->orderBy('created_at', 'desc')
            )
            ->columns([
                TextColumn::make('numero')
                    ->label('N° Pedido')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                    
                TextColumn::make('cliente.nombre')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('fecha_pedido')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
                    
                TextColumn::make('total')
                    ->label('Total')
                    ->money('USD')
                    ->sortable(),
                    
                BadgeColumn::make('estado')
                    ->label('Estado')
                    ->colors([
                        'success' => 'entregado',
                        'warning' => 'confirmado',
                        'info' => 'en_preparacion',
                        'danger' => 'cancelado',
                    ]),
                    
                TextColumn::make('created_at')
                    ->label('Fecha Venta')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                    
                TextColumn::make('user.name')
                    ->label('Vendido por')
                    ->searchable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('estado')
                    ->options([
                        'entregado' => 'Entregado',
                        'confirmado' => 'Confirmado',
                        'en_preparacion' => 'En Preparación',
                        'cancelado' => 'Cancelado',
                    ]),
                \Filament\Tables\Filters\Filter::make('fecha')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('desde')->label('Desde'),
                        \Filament\Forms\Components\DatePicker::make('hasta')->label('Hasta'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['desde'], fn($q, $d) => $q->whereDate('created_at', '>=', $d))
                            ->when($data['hasta'], fn($q, $d) => $q->whereDate('created_at', '<=', $d));
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([15, 25, 50, 100])
            ->actions([
                \Filament\Tables\Actions\Action::make('ver')
                    ->label('Ver')
                    ->icon('heroicon-m-eye')
                    ->url(fn($record) => route('filament.ventas.resources.pedido-ventas.view', $record))
                    ->openUrlInNewTab(),
            ]);
    }
}