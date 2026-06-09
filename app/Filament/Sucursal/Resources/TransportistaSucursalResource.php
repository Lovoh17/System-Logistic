<?php

namespace App\Filament\Sucursal\Resources;

use App\Filament\Sucursal\Resources\TransportistaSucursalResource\Pages;
use App\Models\Almacen;
use App\Models\Transportista;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TransportistaSucursalResource extends Resource
{
    protected static ?string $model = Transportista::class;

    protected static ?string $navigationIcon   = 'heroicon-o-truck';
    protected static ?string $navigationLabel  = 'Transportistas';
    protected static ?string $navigationGroup  = 'Traslados';
    protected static ?int    $navigationSort   = 2;
    protected static ?string $modelLabel       = 'Transportista';
    protected static ?string $pluralModelLabel = 'Transportistas';

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Asignación')->columns(2)->schema([
                Forms\Components\TextInput::make('codigo')
                    ->label('Código')
                    ->default(fn() => Transportista::generarCodigo())
                    ->disabled()->dehydrated()->required(),

                Forms\Components\Select::make('user_id')
                    ->label('Usuario / Conductor')
                    ->relationship('user', 'name')
                    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->name} — {$record->email}")
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\Select::make('almacen_id')
                    ->label('Sucursal')
                    ->options(Almacen::where('activo', true)->pluck('nombre', 'id'))
                    ->searchable()
                    ->required(),

                Forms\Components\Select::make('estado')
                    ->options([
                        'disponible'    => 'Disponible',
                        'en_ruta'       => 'En Ruta',
                        'mantenimiento' => 'Mantenimiento',
                        'inactivo'      => 'Inactivo',
                    ])
                    ->default('disponible')
                    ->required(),
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
                    ]),
                Forms\Components\TextInput::make('vehiculo_placa')
                    ->label('Placa')->maxLength(20),
                Forms\Components\TextInput::make('vehiculo_modelo')
                    ->label('Modelo')->maxLength(80),
                Forms\Components\TextInput::make('capacidad_kg')
                    ->label('Capacidad (kg)')->numeric(),
                Forms\Components\TextInput::make('capacidad_m3')
                    ->label('Capacidad (m³)')->numeric(),
                Forms\Components\Toggle::make('tiene_refrigeracion')->label('Refrigeración'),
                Forms\Components\Toggle::make('tiene_gps')->label('GPS'),
            ]),

            Forms\Components\Section::make('Ubicación GPS')->columns(3)->schema([
                Forms\Components\TextInput::make('latitud')
                    ->numeric()->step(0.00000001)->nullable(),
                Forms\Components\TextInput::make('longitud')
                    ->numeric()->step(0.00000001)->nullable(),
                Forms\Components\TextInput::make('ubicacion_actual')
                    ->label('Descripción de ubicación')->maxLength(255)->nullable(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('codigo')
                    ->badge()->color('gray')->searchable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Conductor')
                    ->searchable()->sortable()
                    ->description(fn($record) => $record->user?->email),

                Tables\Columns\TextColumn::make('almacen.nombre')
                    ->label('Sucursal')
                    ->searchable()->badge()->color('primary'),

                Tables\Columns\TextColumn::make('vehiculo_tipo')
                    ->label('Vehículo')->badge()->color('info'),

                Tables\Columns\TextColumn::make('vehiculo_placa')
                    ->label('Placa')->searchable(),

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
                Tables\Actions\EditAction::make(),
            ])
            ->headerActions([Tables\Actions\CreateAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTransportistasSucursal::route('/'),
            'create' => Pages\CreateTransportistaSucursal::route('/create'),
            'edit'   => Pages\EditTransportistaSucursal::route('/{record}/edit'),
        ];
    }
}
