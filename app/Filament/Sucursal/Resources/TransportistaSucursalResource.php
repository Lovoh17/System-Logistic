<?php

namespace App\Filament\Sucursal\Resources;

use App\Filament\Sucursal\Resources\TransportistaSucursalResource\Pages;
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
            Forms\Components\Section::make('Información del Transportista')->columns(3)->schema([
                Forms\Components\TextInput::make('codigo')
                    ->label('Código')
                    ->default(fn() => Transportista::generarCodigo())
                    ->disabled()->dehydrated()->required()->columnSpan(1),

                Forms\Components\TextInput::make('nombre')
                    ->label('Nombre / Empresa')
                    ->required()->maxLength(150)->columnSpan(2),

                Forms\Components\Select::make('tipo')
                    ->options(['propio' => 'Flota Propia', 'externo' => 'Externo'])
                    ->default('externo')->required()->columnSpan(1),

                Forms\Components\Select::make('estado')
                    ->options([
                        'disponible'    => 'Disponible',
                        'en_ruta'       => 'En Ruta',
                        'mantenimiento' => 'Mantenimiento',
                        'inactivo'      => 'Inactivo',
                    ])
                    ->default('disponible')->required()->columnSpan(1),

                Forms\Components\TextInput::make('email')->email()->columnSpan(1),
                Forms\Components\TextInput::make('telefono')->label('Teléfono')->columnSpan(1),
            ]),

            Forms\Components\Section::make('Vehículo')->columns(3)->schema([
                Forms\Components\Select::make('vehiculo_tipo')
                    ->label('Tipo de Vehículo')
                    ->options(['camion' => 'Camión', 'pickup' => 'Pickup', 'furgon' => 'Furgón', 'moto' => 'Motocicleta', 'otro' => 'Otro'])
                    ->columnSpan(1),
                Forms\Components\TextInput::make('vehiculo_placa')->label('Placa')->maxLength(20)->columnSpan(1),
                Forms\Components\TextInput::make('vehiculo_modelo')->label('Modelo')->maxLength(80)->columnSpan(1),
                Forms\Components\TextInput::make('capacidad_kg')->label('Capacidad (kg)')->numeric()->columnSpan(1),
                Forms\Components\TextInput::make('capacidad_m3')->label('Capacidad (m³)')->numeric()->columnSpan(1),
                Forms\Components\Toggle::make('tiene_refrigeracion')->label('Refrigeración')->columnSpan(1),
                Forms\Components\Toggle::make('tiene_gps')->label('GPS')->columnSpan(1),
            ]),

            Forms\Components\Section::make('Conductor')->columns(3)->schema([
                Forms\Components\TextInput::make('conductor_nombre')->label('Nombre del Conductor')->maxLength(100),
                Forms\Components\TextInput::make('conductor_licencia')->label('N° Licencia')->maxLength(30),
                Forms\Components\TextInput::make('conductor_telefono')->label('Teléfono Conductor')->maxLength(20),
            ]),

            Forms\Components\Section::make('Tarifas')->columns(2)->schema([
                Forms\Components\TextInput::make('tarifa_km')->label('Tarifa por Km ($)')->numeric()->prefix('$')->step(0.01),
                Forms\Components\TextInput::make('tarifa_fija')->label('Tarifa Fija ($)')->numeric()->prefix('$')->step(0.01),
                Forms\Components\Textarea::make('notas')->label('Notas')->rows(2)->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('codigo')->badge()->color('gray')->searchable(),
                Tables\Columns\TextColumn::make('nombre')->searchable()->sortable()
                    ->description(fn($record) => ucfirst($record->tipo)),
                Tables\Columns\TextColumn::make('vehiculo_tipo')->label('Vehículo')->badge()->color('info'),
                Tables\Columns\TextColumn::make('vehiculo_placa')->label('Placa')->searchable(),
                Tables\Columns\TextColumn::make('conductor_nombre')->label('Conductor')->searchable()->toggleable(),
                Tables\Columns\IconColumn::make('tiene_gps')->label('GPS')->boolean(),
                Tables\Columns\IconColumn::make('tiene_refrigeracion')->label('❄️')->boolean(),
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
                Tables\Filters\SelectFilter::make('tipo')
                    ->options(['propio' => 'Propio', 'externo' => 'Externo']),
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
