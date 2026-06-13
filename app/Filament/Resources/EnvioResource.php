<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnvioResource\Pages;
use App\Models\Envio;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EnvioResource extends Resource
{
    protected static ?string $model = Envio::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'Envíos y Transporte';

    protected static ?string $navigationGroup = 'Logística';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Envío';

    protected static ?string $pluralModelLabel = 'Envíos';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Datos del Envío')
                ->icon('heroicon-o-truck')
                ->columns(4)
                ->schema([
                    Forms\Components\TextInput::make('numero')
                        ->label('N° Envío')
                        ->default(fn () => Envio::generarNumero())
                        ->disabled()
                        ->dehydrated()
                        ->required()
                        ->columnSpan(1),

                    Forms\Components\Select::make('pedido_venta_id')
                        ->label('Pedido de Venta')
                        ->relationship('pedidoVenta', 'numero')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->columnSpan(2),

                    Forms\Components\Select::make('estado')
                        ->options([
                            'programado' => 'Programado',
                            'en_preparacion' => 'En Preparación',
                            'despachado' => 'Despachado',
                            'en_transito' => 'En Tránsito',
                            'en_destino' => 'En Destino',
                            'entregado' => 'Entregado',
                            'fallido' => 'Fallido',
                            'devuelto' => 'Devuelto',
                        ])
                        ->default('programado')
                        ->required()
                        ->columnSpan(1),

                    Forms\Components\Select::make('transportista_id')
                        ->label('Transportista / Unidad')
                        ->relationship('transportista', 'id')
                        ->getOptionLabelFromRecordUsing(fn ($record) => ($record->user?->name ?? 'Sin nombre').' — '.($record->vehiculo_placa ?? 'sin placa')
                        )
                        ->searchable()
                        ->preload()
                        ->columnSpan(2),

                    Forms\Components\DatePicker::make('fecha_programada')
                        ->label('Fecha Programada')
                        ->default(now())
                        ->required()
                        ->columnSpan(1),

                    Forms\Components\DateTimePicker::make('fecha_salida')
                        ->label('Fecha/Hora de Salida')
                        ->columnSpan(1),
                ]),

            Forms\Components\Section::make('Origen y Destino')
                ->icon('heroicon-o-map')
                ->columns(2)
                ->schema([
                    Forms\Components\Group::make([
                        Forms\Components\TextInput::make('origen_nombre')->label('Lugar de Origen')->required(),
                        Forms\Components\Textarea::make('origen_direccion')->label('Dirección de Origen')->rows(2),
                    ])->columnSpan(1),

                    Forms\Components\Group::make([
                        Forms\Components\TextInput::make('destino_nombre')->label('Destino / Receptor')->required(),
                        Forms\Components\TextInput::make('destino_departamento')->label('Departamento'),
                        Forms\Components\TextInput::make('destino_municipio')->label('Municipio'),
                        Forms\Components\Textarea::make('destino_direccion')->label('Dirección de Destino')->rows(2),
                    ])->columnSpan(1),
                ]),

            Forms\Components\Section::make('Detalles de Carga')
                ->icon('heroicon-o-scale')
                ->columns(4)
                ->schema([
                    Forms\Components\TextInput::make('peso_total_kg')
                        ->label('Peso Total (kg)')->numeric()->step(0.001)->columnSpan(1),

                    Forms\Components\TextInput::make('volumen_total_m3')
                        ->label('Volumen (m³)')->numeric()->step(0.001)->columnSpan(1),

                    Forms\Components\TextInput::make('distancia_km')
                        ->label('Distancia (km)')->numeric()->step(0.01)->columnSpan(1),

                    Forms\Components\TextInput::make('costo_envio')
                        ->label('Costo de Envío ($)')->numeric()->prefix('$')->default(0)->columnSpan(1),
                ]),

            Forms\Components\Section::make('Entrega')
                ->icon('heroicon-o-check-circle')
                ->columns(3)
                ->schema([
                    Forms\Components\DateTimePicker::make('fecha_entrega_estimada')
                        ->label('Entrega Estimada')->columnSpan(1),

                    Forms\Components\DateTimePicker::make('fecha_entrega_real')
                        ->label('Entrega Real')->columnSpan(1),

                    Forms\Components\TextInput::make('numero_seguimiento')
                        ->label('N° de Seguimiento')->maxLength(50)->columnSpan(1),

                    Forms\Components\TextInput::make('firma_receptor')
                        ->label('Recibido por')->maxLength(150)->columnSpan(1),

                    Forms\Components\FileUpload::make('foto_entrega')
                        ->label('Foto de Entrega')->image()->directory('entregas')->columnSpan(2),

                    Forms\Components\Textarea::make('observaciones')
                        ->label('Observaciones')->rows(3)->columnSpanFull(),

                    Forms\Components\Textarea::make('motivo_fallo')
                        ->label('Motivo de Fallo (si aplica)')
                        ->rows(2)
                        ->columnSpanFull()
                        ->visible(fn (Forms\Get $get) => in_array($get('estado'), ['fallido', 'devuelto'])),
                ]),

            Forms\Components\Section::make('Seguimiento GPS')
                ->icon('heroicon-o-map-pin')
                ->columns(2)
                ->collapsed()
                ->schema([
                    Forms\Components\TextInput::make('latitud_actual')
                        ->label('Latitud')->numeric()->step(0.00000001),
                    Forms\Components\TextInput::make('longitud_actual')
                        ->label('Longitud')->numeric()->step(0.00000001),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero')
                    ->label('N° Envío')
                    ->searchable()->sortable()
                    ->badge()->color('primary'),

                Tables\Columns\TextColumn::make('pedidoVenta.numero')
                    ->label('Pedido')
                    ->searchable()
                    ->badge()->color('gray'),

                Tables\Columns\TextColumn::make('pedidoVenta.cliente.nombre')
                    ->label('Cliente')
                    ->searchable(),

                Tables\Columns\TextColumn::make('transportista.user.name')
                    ->label('Transportista')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('destino_municipio')
                    ->label('Destino')
                    ->description(fn ($record) => $record->destino_departamento),

                Tables\Columns\TextColumn::make('fecha_programada')
                    ->label('Programado')
                    ->date('d/m/Y')->sortable(),

                Tables\Columns\TextColumn::make('fecha_entrega_estimada')
                    ->label('Est. Entrega')
                    ->dateTime('d/m/Y H:i')->sortable()->toggleable(),

                Tables\Columns\BadgeColumn::make('estado')
                    ->colors([
                        'gray' => 'programado',
                        'warning' => 'en_preparacion',
                        'info' => 'despachado',
                        'primary' => 'en_transito',
                        'indigo' => 'en_destino',
                        'success' => 'entregado',
                        'danger' => 'fallido',
                        'orange' => 'devuelto',
                    ]),

                Tables\Columns\TextColumn::make('costo_envio')
                    ->label('Costo')->money('USD')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->options([
                        'programado' => 'Programado',
                        'en_preparacion' => 'En Preparación',
                        'despachado' => 'Despachado',
                        'en_transito' => 'En Tránsito',
                        'en_destino' => 'En Destino',
                        'entregado' => 'Entregado',
                        'fallido' => 'Fallido',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('transportista_id')
                    ->label('Transportista')
                    ->relationship('transportista', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record) => ($record->user?->name ?? 'Sin nombre').' — '.($record->vehiculo_placa ?? 'sin placa')
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Estado del Envío')
                ->columns(4)
                ->schema([
                    Infolists\Components\TextEntry::make('numero')->badge()->color('primary'),
                    Infolists\Components\TextEntry::make('estado')
                        ->badge()
                        ->color(fn ($record) => match ($record->estado) {
                            'programado' => 'gray',
                            'en_preparacion' => 'warning',
                            'despachado' => 'info',
                            'en_transito' => 'primary',
                            'en_destino' => 'indigo',
                            'entregado' => 'success',
                            'fallido' => 'danger',
                            default => 'gray',
                        }),
                    Infolists\Components\TextEntry::make('pedidoVenta.numero')
                        ->label('Pedido')->badge()->color('gray'),
                    Infolists\Components\TextEntry::make('transportista.user.name')
                        ->label('Transportista'),
                ]),

            Infolists\Components\Section::make('Ruta')
                ->columns(2)
                ->schema([
                    Infolists\Components\TextEntry::make('origen_nombre')->label('Origen'),
                    Infolists\Components\TextEntry::make('destino_nombre')->label('Destino'),
                    Infolists\Components\TextEntry::make('origen_direccion')->label('Dir. Origen'),
                    Infolists\Components\TextEntry::make('destino_direccion')->label('Dir. Destino'),
                ]),

            Infolists\Components\Section::make('Historial de Seguimiento')
                ->schema([
                    Infolists\Components\RepeatableEntry::make('seguimientos')
                        ->label('')
                        ->schema([
                            Infolists\Components\TextEntry::make('fecha_hora')->dateTime('d/m/Y H:i'),
                            Infolists\Components\TextEntry::make('evento')->weight('bold'),
                            Infolists\Components\TextEntry::make('ubicacion'),
                            Infolists\Components\TextEntry::make('descripcion'),
                        ])
                        ->columns(4),
                ]),
        ]);
    }

    public static function getNavigationBadge(): ?string
    {
        $enRuta = static::getModel()::whereIn('estado', ['despachado', 'en_transito', 'en_destino'])->count();

        return $enRuta > 0 ? (string) $enRuta : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEnvio::route('/'),
            'create' => Pages\CreateEnvio::route('/create'),
            'view' => Pages\ViewEnvio::route('/{record}'),
            'edit' => Pages\EditEnvio::route('/{record}/edit'),
        ];
    }
}
