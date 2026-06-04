<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AlmacenResource\Pages;
use App\Models\Almacen;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AlmacenResource extends Resource
{
    protected static ?string $model          = Almacen::class;
    protected static ?string $navigationIcon  = 'heroicon-o-building-storefront';
    protected static ?string $navigationLabel = 'Sucursales';
    protected static ?string $navigationGroup = 'Administración';
    protected static ?int    $navigationSort  = 10;
    protected static ?string $modelLabel      = 'Sucursal';
    protected static ?string $pluralModelLabel = 'Sucursales';
    protected static ?string $slug            = 'sucursales';

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Información General')
                ->schema([
                    Forms\Components\TextInput::make('codigo')
                        ->label('Código')
                        ->required()
                        ->maxLength(20)
                        ->unique(ignoreRecord: true)
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('nombre')
                        ->label('Nombre de la Sucursal')
                        ->required()
                        ->maxLength(100)
                        ->columnSpan(2),

                    Forms\Components\Textarea::make('direccion')
                        ->label('Dirección')
                        ->rows(2)
                        ->maxLength(500)
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('responsable')
                        ->label('Responsable')
                        ->maxLength(100),

                    Forms\Components\TextInput::make('telefono')
                        ->label('Teléfono')
                        ->tel()
                        ->maxLength(20),

                    Forms\Components\Toggle::make('es_principal')
                        ->label('Sucursal Principal')
                        ->helperText('Solo puede haber una sucursal principal.')
                        ->inline(false),

                    Forms\Components\Toggle::make('activo')
                        ->label('Activa')
                        ->default(true)
                        ->inline(false),
                ])
                ->columns(3),

            Forms\Components\Section::make('Ubicación Geográfica')
                ->description('Coordenadas decimales (WGS 84). Se usan para calcular distancias entre sucursales y mostrarlas en el mapa.')
                ->icon('heroicon-o-map-pin')
                ->schema([
                    Forms\Components\TextInput::make('latitud')
                        ->label('Latitud')
                        ->numeric()
                        ->step(0.0000001)
                        ->minValue(-90)
                        ->maxValue(90)
                        ->placeholder('13.6929000')
                        ->helperText('Valor decimal, ej: 13.4833'),

                    Forms\Components\TextInput::make('longitud')
                        ->label('Longitud')
                        ->numeric()
                        ->step(0.0000001)
                        ->minValue(-180)
                        ->maxValue(180)
                        ->placeholder('-88.1960000')
                        ->helperText('Valor decimal, ej: -88.1833'),

                    Forms\Components\Placeholder::make('ayuda_coordenadas')
                        ->label('')
                        ->content('Para obtener coordenadas: abra Google Maps, haga clic derecho sobre la ubicación y seleccione "¿Qué hay aquí?". Copie los valores (latitud, longitud) que aparecen en la parte inferior.')
                        ->columnSpanFull(),
                ])
                ->columns(2),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('codigo')
                    ->label('Código')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('nombre')
                    ->label('Sucursal')
                    ->searchable()
                    ->sortable()
                    ->description(fn($record) => $record->direccion
                        ? \Illuminate\Support\Str::limit($record->direccion, 60)
                        : null),

                Tables\Columns\TextColumn::make('responsable')
                    ->label('Responsable')
                    ->searchable()
                    ->toggleable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->toggleable()
                    ->placeholder('—'),

                Tables\Columns\IconColumn::make('es_principal')
                    ->label('Principal')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('activo')
                    ->label('Activa')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('coordenadas_estado')
                    ->label('GPS')
                    ->getStateUsing(fn($record) => $record->tieneCoordenadas() ? 'Ubicada' : 'Sin GPS')
                    ->badge()
                    ->color(fn($state) => $state === 'Ubicada' ? 'success' : 'warning')
                    ->icon(fn($state) => $state === 'Ubicada'
                        ? 'heroicon-m-map-pin'
                        : 'heroicon-m-exclamation-triangle'),

                Tables\Columns\TextColumn::make('latitud')
                    ->label('Latitud')
                    ->numeric(5)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('longitud')
                    ->label('Longitud')
                    ->numeric(5)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('—'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('activo')
                    ->label('Estado')
                    ->placeholder('Todas')
                    ->trueLabel('Solo activas')
                    ->falseLabel('Solo inactivas'),

                Tables\Filters\TernaryFilter::make('es_principal')
                    ->label('Tipo')
                    ->placeholder('Todas')
                    ->trueLabel('Principal')
                    ->falseLabel('Sucursales'),

                Tables\Filters\Filter::make('con_coordenadas')
                    ->label('Con coordenadas GPS')
                    ->query(fn($query) => $query->whereNotNull('latitud')->whereNotNull('longitud')),

                Tables\Filters\Filter::make('sin_coordenadas')
                    ->label('Sin coordenadas GPS')
                    ->query(fn($query) => $query->where(
                        fn($q) => $q->whereNull('latitud')->orWhereNull('longitud')
                    )),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('nombre', 'asc')
            ->emptyStateIcon('heroicon-o-building-storefront')
            ->emptyStateHeading('No hay sucursales registradas')
            ->emptyStateDescription('Cree la primera sucursal con el botón "Nueva Sucursal".');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAlmacenes::route('/'),
            'create' => Pages\CreateAlmacen::route('/create'),
            'edit'   => Pages\EditAlmacen::route('/{record}/edit'),
        ];
    }
}
