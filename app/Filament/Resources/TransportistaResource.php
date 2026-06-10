<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransportistaResource\Pages;
use App\Models\Almacen;
use App\Models\Transportista;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class TransportistaResource extends Resource
{
    protected static ?string $model = Transportista::class;

    protected static ?string $navigationIcon   = 'heroicon-o-truck';
    protected static ?string $navigationLabel  = 'Transportistas';
    protected static ?string $navigationGroup  = 'Logística';
    protected static ?int    $navigationSort   = 2;
    protected static ?string $modelLabel       = 'Transportista';
    protected static ?string $pluralModelLabel = 'Transportistas';

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Asignación')->columns(3)->schema([
                Forms\Components\TextInput::make('codigo')
                    ->label('Código')
                    ->default(fn() => Transportista::generarCodigo())
                    ->disabled()->dehydrated()->required()
                    ->columnSpan(1),

                Forms\Components\Select::make('user_id')
                    ->label('Usuario / Conductor')
                    ->relationship('user', 'name')
                    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->name} — {$record->email}")
                    ->searchable()
                    ->preload()
                    ->required()
                    ->columnSpan(1),

                Forms\Components\Select::make('almacen_id')
                    ->label('Sucursal asignada')
                    ->options(Almacen::where('activo', true)->pluck('nombre', 'id'))
                    ->searchable()
                    ->required()
                    ->columnSpan(1),

                Forms\Components\Select::make('estado')
                    ->options([
                        'disponible'    => 'Disponible',
                        'en_ruta'       => 'En Ruta',
                        'mantenimiento' => 'Mantenimiento',
                        'inactivo'      => 'Inactivo',
                    ])
                    ->default('disponible')
                    ->required()
                    ->columnSpan(1),
            ]),

            Forms\Components\Section::make('Vehículo')->columns(3)->schema([
                Forms\Components\Select::make('vehiculo_tipo')
                    ->label('Tipo')
                    ->options([
                        'camion' => 'Camión',
                        'pickup' => 'Pickup',
                        'furgon' => 'Furgón',
                        'moto'   => 'Motocicleta',
                        'otro'   => 'Otro',
                    ])
                    ->columnSpan(1),

                Forms\Components\TextInput::make('vehiculo_placa')
                    ->label('Placa')->maxLength(20)->columnSpan(1),

                Forms\Components\TextInput::make('vehiculo_modelo')
                    ->label('Modelo')->maxLength(80)->columnSpan(1),

                Forms\Components\TextInput::make('capacidad_kg')
                    ->label('Capacidad (kg)')->numeric()->columnSpan(1),

                Forms\Components\TextInput::make('capacidad_m3')
                    ->label('Capacidad (m³)')->numeric()->columnSpan(1),

                Forms\Components\Toggle::make('tiene_refrigeracion')
                    ->label('Refrigeración')->columnSpan(1),

                Forms\Components\Toggle::make('tiene_gps')
                    ->label('GPS')->columnSpan(1),
            ]),

            Forms\Components\Section::make('Ubicación GPS')->columns(3)->schema([
                Forms\Components\TextInput::make('latitud')
                    ->numeric()->step(0.00000001)->nullable()->columnSpan(1),

                Forms\Components\TextInput::make('longitud')
                    ->numeric()->step(0.00000001)->nullable()->columnSpan(1),

                Forms\Components\TextInput::make('ubicacion_actual')
                    ->label('Descripción de ubicación')->maxLength(255)->nullable()->columnSpan(1),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('codigo')
                    ->badge()->color('gray')->searchable()->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Conductor')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('almacen.nombre')
                    ->label('Sucursal')
                    ->searchable()->sortable()->badge()->color('primary'),

                Tables\Columns\TextColumn::make('vehiculo_tipo')
                    ->label('Vehículo')->badge()->color('info'),

                Tables\Columns\TextColumn::make('vehiculo_placa')
                    ->label('Placa')->searchable(),

                Tables\Columns\TextColumn::make('vehiculo_modelo')
                    ->label('Modelo')->toggleable(),

                Tables\Columns\TextColumn::make('capacidad_kg')
                    ->label('Cap. kg')->toggleable(),

                Tables\Columns\IconColumn::make('tiene_gps')
                    ->label('GPS')->boolean(),

                Tables\Columns\IconColumn::make('tiene_refrigeracion')
                    ->label('❄️')->boolean(),

                Tables\Columns\TextColumn::make('ubicacion_actual')
                    ->label('Ubicación')
                    ->toggleable()
                    ->description(fn($record) => $record->latitud
                        ? "{$record->latitud}, {$record->longitud}"
                        : null
                    ),

                Tables\Columns\BadgeColumn::make('estado')->colors([
                    'success' => 'disponible',
                    'warning' => 'en_ruta',
                    'danger'  => 'mantenimiento',
                    'gray'    => 'inactivo',
                ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')->options([
                    'disponible'    => 'Disponible',
                    'en_ruta'       => 'En Ruta',
                    'mantenimiento' => 'Mantenimiento',
                    'inactivo'      => 'Inactivo',
                ]),
                Tables\Filters\SelectFilter::make('almacen_id')
                    ->label('Sucursal')
                    ->options(Almacen::pluck('nombre', 'id')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Conductor')
                ->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('codigo')
                        ->badge()->color('gray'),

                    Infolists\Components\TextEntry::make('user.name')
                        ->label('Nombre'),

                    Infolists\Components\TextEntry::make('user.email')
                        ->label('Email'),

                    Infolists\Components\TextEntry::make('almacen.nombre')
                        ->label('Sucursal')->badge()->color('primary'),

                    Infolists\Components\TextEntry::make('estado')
                        ->badge()
                        ->color(fn($record) => match($record->estado) {
                            'disponible'    => 'success',
                            'en_ruta'       => 'warning',
                            'mantenimiento' => 'danger',
                            default          => 'gray',
                        }),
                ]),

            Infolists\Components\Section::make('Vehículo')
                ->columns(4)
                ->schema([
                    Infolists\Components\TextEntry::make('vehiculo_tipo')
                        ->label('Tipo')->badge()->color('info'),

                    Infolists\Components\TextEntry::make('vehiculo_placa')
                        ->label('Placa'),

                    Infolists\Components\TextEntry::make('vehiculo_modelo')
                        ->label('Modelo'),

                    Infolists\Components\TextEntry::make('capacidad_kg')
                        ->label('Cap. kg')
                        ->suffix(' kg'),

                    Infolists\Components\TextEntry::make('capacidad_m3')
                        ->label('Cap. m³')
                        ->suffix(' m³'),

                    Infolists\Components\IconEntry::make('tiene_gps')
                        ->label('GPS')->boolean(),

                    Infolists\Components\IconEntry::make('tiene_refrigeracion')
                        ->label('Refrigeración')->boolean(),
                ]),

            Infolists\Components\Section::make('Ubicación GPS')
                ->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('ubicacion_actual')
                        ->label('Descripción'),

                    Infolists\Components\TextEntry::make('latitud'),

                    Infolists\Components\TextEntry::make('longitud'),
                ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTransportistas::route('/'),
            'create' => Pages\CreateTransportista::route('/create'),
            'view'   => Pages\ViewTransportista::route('/{record}'),
            'edit'   => Pages\EditTransportista::route('/{record}/edit'),

        ];
    }
}