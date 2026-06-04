<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TrasladoResource\Pages;
use App\Models\Traslado;
use App\Models\Almacen;
use App\Models\InventarioAlmacen;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class TrasladoResource extends Resource
{
    protected static ?string $model = Traslado::class;

    protected static ?string $navigationIcon   = 'heroicon-o-arrow-path';
    protected static ?string $navigationLabel  = 'Traslados';
    protected static ?string $navigationGroup  = 'Logística';
    protected static ?int    $navigationSort   = 2;
    protected static ?string $modelLabel       = 'Traslado';
    protected static ?string $pluralModelLabel = 'Traslados';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Traslado')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('numero')
                        ->label('N° Traslado')
                        ->default(fn() => Traslado::generarNumero())
                        ->disabled()->dehydrated()->required()
                        ->columnSpan(1),

                    Forms\Components\Select::make('almacen_origen_id')
                        ->label('Sucursal Origen')
                        ->options(Almacen::where('activo', true)->pluck('nombre', 'id'))
                        ->required()->searchable()->columnSpan(1),

                    Forms\Components\Select::make('almacen_destino_id')
                        ->label('Sucursal Destino')
                        ->options(Almacen::where('activo', true)->pluck('nombre', 'id'))
                        ->required()->searchable()->columnSpan(1),

                    Forms\Components\Select::make('estado')
                        ->options([
                            'sugerido'   => 'Sugerido',
                            'aprobado'   => 'Aprobado',
                            'completado' => 'Completado',
                            'cancelado'  => 'Cancelado',
                        ])
                        ->default('sugerido')->required()->columnSpan(1),

                    Forms\Components\DateTimePicker::make('fecha_aprobacion')
                        ->label('Fecha de Aprobación')
                        ->columnSpan(1),

                    Forms\Components\DateTimePicker::make('fecha_completado')
                        ->label('Fecha Completado')
                        ->columnSpan(1),
                ]),

            Forms\Components\Section::make('Productos a Trasladar')
                ->schema([
                    Forms\Components\Repeater::make('items')
                        ->relationship()
                        ->label('')
                        ->columns(3)
                        ->defaultItems(1)
                        ->schema([
                            Forms\Components\Select::make('producto_id')
                                ->label('Producto')
                                ->options(\App\Models\Producto::activo()->pluck('nombre', 'id'))
                                ->required()->searchable()->columnSpan(1),

                            Forms\Components\TextInput::make('cantidad_sugerida')
                                ->label('Cantidad Sugerida')
                                ->numeric()->required()->minValue(0.001)->step(0.001)
                                ->columnSpan(1),

                            Forms\Components\TextInput::make('cantidad_real')
                                ->label('Cantidad Real')
                                ->numeric()->minValue(0)->step(0.001)
                                ->columnSpan(1),

                            Forms\Components\TextInput::make('lote')
                                ->label('Lote')->columnSpan(1),

                            Forms\Components\DatePicker::make('fecha_vencimiento')
                                ->label('Fecha Vencimiento')->columnSpan(1),

                            Forms\Components\Textarea::make('notas')
                                ->label('Notas')->rows(2)->columnSpan(1),
                        ])
                        ->addActionLabel('+ Agregar Producto'),
                ]),

            Forms\Components\Section::make('Información Adicional')
                ->schema([
                    Forms\Components\Textarea::make('motivo')
                        ->label('Motivo')->rows(2)->required(),
                ]),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([

            Infolists\Components\Section::make('Traslado')
                ->columns(4)
                ->schema([
                    Infolists\Components\TextEntry::make('numero')
                        ->label('N° Traslado')
                        ->badge()->color('primary'),

                    Infolists\Components\TextEntry::make('estado')
                        ->badge()
                        ->color(fn($state) => match($state) {
                            'sugerido'   => 'warning',
                            'aprobado'   => 'info',
                            'completado' => 'success',
                            'cancelado'  => 'danger',
                            default      => 'gray',
                        })
                        ->formatStateUsing(fn($state) => match($state) {
                            'sugerido'   => 'Sugerido',
                            'aprobado'   => 'Aprobado',
                            'completado' => 'Completado',
                            'cancelado'  => 'Cancelado',
                            default      => ucfirst($state),
                        }),

                    Infolists\Components\TextEntry::make('almacenOrigen.nombre')
                        ->label('Sucursal Origen')
                        ->icon('heroicon-m-building-storefront'),

                    Infolists\Components\TextEntry::make('almacenDestino.nombre')
                        ->label('Sucursal Destino')
                        ->icon('heroicon-m-building-storefront'),
                ]),

            Infolists\Components\Section::make('Productos')
                ->schema([
                    Infolists\Components\RepeatableEntry::make('items')
                        ->label('')
                        ->columns(4)
                        ->schema([
                            Infolists\Components\TextEntry::make('producto.nombre')
                                ->label('Producto'),
                            Infolists\Components\TextEntry::make('cantidad_sugerida')
                                ->label('Cant. Sugerida')
                                ->numeric(3),
                            Infolists\Components\TextEntry::make('cantidad_real')
                                ->label('Cant. Real')
                                ->numeric(3)->placeholder('—'),
                            Infolists\Components\TextEntry::make('lote')
                                ->label('Lote')->placeholder('—'),
                            Infolists\Components\TextEntry::make('fecha_vencimiento')
                                ->label('Vencimiento')->date('d/m/Y')->placeholder('—'),
                            Infolists\Components\TextEntry::make('notas')
                                ->label('Notas')->placeholder('—')->columnSpan(2),
                        ]),
                ]),

            Infolists\Components\Section::make('Fechas y Responsables')
                ->columns(4)
                ->schema([
                    Infolists\Components\TextEntry::make('created_at')
                        ->label('Creado')->dateTime('d/m/Y H:i'),

                    Infolists\Components\TextEntry::make('fecha_aprobacion')
                        ->label('Aprobado')->dateTime('d/m/Y H:i')->placeholder('—'),

                    Infolists\Components\TextEntry::make('fecha_completado')
                        ->label('Completado')->dateTime('d/m/Y H:i')->placeholder('—'),

                    Infolists\Components\TextEntry::make('creadoPor.name')
                        ->label('Creado por'),
                ]),

            Infolists\Components\Section::make('Información Adicional')
                ->collapsed()
                ->schema([
                    Infolists\Components\TextEntry::make('motivo')
                        ->label('Motivo')->columnSpanFull(),

                    Infolists\Components\TextEntry::make('aprobadoPor.name')
                        ->label('Aprobado por')->placeholder('—'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero')
                    ->label('N° Traslado')
                    ->searchable()->sortable()
                    ->badge()->color('primary'),

                Tables\Columns\TextColumn::make('almacenOrigen.nombre')
                    ->label('Origen')
                    ->badge()->color('gray')->sortable(),

                Tables\Columns\TextColumn::make('almacenDestino.nombre')
                    ->label('Destino')
                    ->badge()->color('gray')->sortable(),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('Productos')
                    ->counts('items')
                    ->badge()->color('primary')->alignCenter(),

                Tables\Columns\TextColumn::make('creadoPor.name')
                    ->label('Creado por')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->date('d/m/Y')->sortable(),

                Tables\Columns\TextColumn::make('estado')
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'sugerido'   => 'warning',
                        'aprobado'   => 'info',
                        'completado' => 'success',
                        'cancelado'  => 'danger',
                        default      => 'gray',
                    })
                    ->formatStateUsing(fn($state) => match($state) {
                        'sugerido'   => 'Sugerido',
                        'aprobado'   => 'Aprobado',
                        'completado' => 'Completado',
                        'cancelado'  => 'Cancelado',
                        default      => ucfirst($state),
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->options([
                        'sugerido'   => 'Sugerido',
                        'aprobado'   => 'Aprobado',
                        'completado' => 'Completado',
                        'cancelado'  => 'Cancelado',
                    ]),
                Tables\Filters\SelectFilter::make('almacen_origen_id')
                    ->label('Sucursal Origen')
                    ->relationship('almacenOrigen', 'nombre'),
                Tables\Filters\SelectFilter::make('almacen_destino_id')
                    ->label('Sucursal Destino')
                    ->relationship('almacenDestino', 'nombre'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('aprobar')
                    ->label('Aprobar')
                    ->icon('heroicon-m-check')->color('info')
                    ->visible(fn($record) => $record->estado === 'sugerido')
                    ->requiresConfirmation()
                    ->modalHeading('¿Aprobar este traslado?')
                    ->action(function ($record) {
                        $record->update([
                            'estado'           => 'aprobado',
                            'aprobado_por'     => auth()->id(),
                            'fecha_aprobacion' => now(),
                        ]);
                        Notification::make()->success()->title('Traslado aprobado')->send();
                    }),

                Tables\Actions\Action::make('completar')
                    ->label('Completar')
                    ->icon('heroicon-m-check-circle')->color('success')
                    ->visible(fn($record) => $record->estado === 'aprobado')
                    ->modalHeading('Completar Traslado')
                    ->modalWidth('lg')
                    ->form(fn($record) =>
                    $record->items->flatMap(fn($item, $i) => [
                        Forms\Components\Hidden::make("items.{$i}.item_id")
                            ->default($item->id),

                        Forms\Components\Placeholder::make("items.{$i}.producto_nombre")
                            ->label($item->producto->nombre ?? '—')
                            ->content('Sugerido: ' . number_format($item->cantidad_sugerida, 3)),

                        Forms\Components\TextInput::make("items.{$i}.cantidad_real")
                            ->label('Cantidad real recibida')
                            ->numeric()->required()->minValue(0)
                            ->default($item->cantidad_sugerida)
                            ->step(0.001),
                    ])->values()->toArray()
                    )
                    ->action(function ($record, array $data) {
                        foreach ($data['items'] ?? [] as $itemData) {
                            $item = \App\Models\TrasladoItem::find($itemData['item_id']);
                            if (!$item) continue;

                            $cantReal = floatval($itemData['cantidad_real']);
                            $item->update(['cantidad_real' => $cantReal]);

                            $inventario = InventarioAlmacen::where('producto_id', $item->producto_id)
                                ->where('almacen_id', $record->almacen_destino_id)
                                ->first();

                            if ($inventario) {
                                $inventario->increment('stock_actual', $cantReal);
                            } else {
                                InventarioAlmacen::create([
                                    'producto_id'   => $item->producto_id,
                                    'almacen_id'    => $record->almacen_destino_id,
                                    'stock_actual'  => $cantReal,
                                    'stock_minimo'  => 0,
                                    'stock_maximo'  => 999999,
                                    'punto_reorden' => 0,
                                ]);
                            }
                        }

                        $record->update([
                            'estado'           => 'completado',
                            'fecha_completado' => now(),
                        ]);

                        Notification::make()->success()
                            ->title('Traslado completado. Inventario actualizado.')
                            ->send();
                    }),

                Tables\Actions\Action::make('cancelar')
                    ->label('Cancelar')
                    ->icon('heroicon-m-x-circle')->color('danger')
                    ->visible(fn($record) => !in_array($record->estado, ['completado', 'cancelado']))
                    ->requiresConfirmation()
                    ->modalHeading('¿Cancelar este traslado?')
                    ->action(function ($record) {
                        $record->update(['estado' => 'cancelado']);
                        Notification::make()->success()->title('Traslado cancelado')->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTraslados::route('/'),
            'create' => Pages\CreateTraslado::route('/create'),
            'view'   => Pages\ViewTraslado::route('/{record}'),
            'edit'   => Pages\EditTraslado::route('/{record}/edit'),
        ];
    }
}