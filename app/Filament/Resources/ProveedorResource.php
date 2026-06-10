<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProveedorResource\Pages;
use App\Models\Proveedor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ProveedorResource extends Resource
{
    protected static ?string $model = Proveedor::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationLabel = 'Proveedores';
    protected static ?string $navigationGroup = 'Gestión de Socios';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'Proveedor';
    protected static ?string $pluralModelLabel = 'Proveedores';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Información Principal')
                ->icon('heroicon-o-building-storefront')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('codigo')
                        ->label('Código')
                        ->default(fn() => Proveedor::generarCodigo())
                        ->disabled()
                        ->dehydrated()
                        ->required()
                        ->maxLength(20)
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('nombre')
                        ->label('Nombre Comercial')
                        ->required()
                        ->maxLength(150)
                        ->columnSpan(2),

                    Forms\Components\TextInput::make('razon_social')
                        ->label('Razón Social')
                        ->maxLength(200)
                        ->columnSpan(2),

                    Forms\Components\Select::make('categoria')
                        ->label('Categoría')
                        ->options([
                            'general'       => 'General',
                            'materia_prima' => 'Materia Prima',
                            'servicios'     => 'Servicios',
                        ])
                        ->default('general')
                        ->required()
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('nit')
                        ->label('NIT')
                        ->maxLength(20)
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('ruc')
                        ->label('RUC / Registro')
                        ->maxLength(20)
                        ->columnSpan(1),

                    Forms\Components\Select::make('estado')
                        ->options([
                            'activo'    => 'Activo',
                            'inactivo'  => 'Inactivo',
                            'suspendido' => 'Suspendido',
                        ])
                        ->default('activo')
                        ->required()
                        ->columnSpan(1),
                ]),

            Forms\Components\Section::make('Contacto')
                ->icon('heroicon-o-phone')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('email')
                        ->label('Correo Electrónico')
                        ->email()
                        ->maxLength(150)
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('telefono')
                        ->label('Teléfono')
                        ->tel()
                        ->maxLength(20)
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('celular')
                        ->label('Celular / WhatsApp')
                        ->maxLength(20)
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('contacto_nombre')
                        ->label('Nombre del Contacto')
                        ->maxLength(100)
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('contacto_email')
                        ->label('Email del Contacto')
                        ->email()
                        ->maxLength(150)
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('contacto_telefono')
                        ->label('Teléfono del Contacto')
                        ->maxLength(20)
                        ->columnSpan(1),
                ]),

            Forms\Components\Section::make('Ubicación')
                ->icon('heroicon-o-map-pin')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('pais')
                        ->label('País')
                        ->default('El Salvador')
                        ->maxLength(80)
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('departamento')
                        ->label('Departamento')
                        ->maxLength(80)
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('municipio')
                        ->label('Municipio')
                        ->maxLength(80)
                        ->columnSpan(1),

                    Forms\Components\Textarea::make('direccion')
                        ->label('Dirección')
                        ->rows(2)
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Condiciones Comerciales')
                ->icon('heroicon-o-currency-dollar')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('tiempo_entrega_dias')
                        ->label('Tiempo de Entrega (días)')
                        ->numeric()
                        ->default(3)
                        ->minValue(0)
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('calificacion')
                        ->label('Calificación (0-5)')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->maxValue(5)
                        ->step(0.5)
                        ->columnSpan(1),

                    Forms\Components\Textarea::make('notas')
                        ->label('Notas')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('codigo')
                    ->label('Código')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('nombre')
                    ->label('Proveedor')
                    ->searchable()
                    ->sortable()
                    ->description(fn($record) => $record->categoria_label),

                Tables\Columns\TextColumn::make('contacto_nombre')
                    ->label('Contacto')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('tiempo_entrega_dias')
                    ->label('Entrega')
                    ->sortable()
                    ->suffix(' días')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('calificacion')
                    ->label('Calif.')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color(fn($state) => match(true) {
                        $state >= 4  => 'success',
                        $state >= 2  => 'warning',
                        default      => 'danger',
                    }),

                Tables\Columns\TextColumn::make('productos_count')
                    ->label('Productos')
                    ->counts('productos')
                    ->alignCenter()
                    ->badge()
                    ->color('info'),

                Tables\Columns\BadgeColumn::make('estado')
                    ->label('Estado')
                    ->colors([
                        'success' => 'activo',
                        'gray'    => 'inactivo',
                        'danger'  => 'suspendido',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->options([
                        'activo'    => 'Activo',
                        'inactivo'  => 'Inactivo',
                        'suspendido' => 'Suspendido',
                    ]),
                Tables\Filters\SelectFilter::make('categoria')
                    ->options([
                        'general'       => 'General',
                        'materia_prima' => 'Materia Prima',
                        'servicios'     => 'Servicios',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('nombre');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Datos del Proveedor')
                ->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('codigo')->badge()->color('gray'),
                    Infolists\Components\TextEntry::make('nombre')->weight('bold'),
                    Infolists\Components\TextEntry::make('razon_social'),
                    Infolists\Components\TextEntry::make('categoria_label')->label('Categoría'),
                    Infolists\Components\TextEntry::make('nit'),
                    Infolists\Components\TextEntry::make('estado')
                        ->badge()
                        ->color(fn($record) => $record->estado_color),
                ]),
            Infolists\Components\Section::make('Contacto')
                ->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('email')->copyable(),
                    Infolists\Components\TextEntry::make('telefono'),
                    Infolists\Components\TextEntry::make('celular'),
                    Infolists\Components\TextEntry::make('contacto_nombre')->label('Contacto Principal'),
                ]),
        ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProveedores::route('/'),
            'create' => Pages\CreateProveedor::route('/create'),
            'view'   => Pages\ViewProveedor::route('/{record}'),
            'edit'   => Pages\EditProveedor::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('estado', 'activo')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
