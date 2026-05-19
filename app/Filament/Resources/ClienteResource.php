<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClienteResource\Pages;
use App\Models\Cliente;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ClienteResource extends Resource
{
    protected static ?string $model = Cliente::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Clientes';
    protected static ?string $navigationGroup = 'Gestión de Socios';
    protected static ?int $navigationSort = 2;
    protected static ?string $modelLabel = 'Cliente';
    protected static ?string $pluralModelLabel = 'Clientes';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Datos del Cliente')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('codigo')
                        ->label('Código')
                        ->default(fn() => Cliente::generarCodigo())
                        ->disabled()
                        ->dehydrated()
                        ->required()
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('nombre')
                        ->label('Nombre / Razón Comercial')
                        ->required()
                        ->maxLength(150)
                        ->columnSpan(2),

                    Forms\Components\TextInput::make('razon_social')
                        ->label('Razón Social Legal')
                        ->maxLength(200)
                        ->columnSpan(2),

                    Forms\Components\Select::make('tipo')
                        ->label('Tipo de Cliente')
                        ->options([
                            'minorista'   => 'Minorista',
                            'mayorista'   => 'Mayorista',
                            'corporativo' => 'Corporativo',
                        ])
                        ->default('minorista')
                        ->required()
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('nit')
                        ->label('NIT')
                        ->maxLength(20)
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('dui')
                        ->label('DUI')
                        ->maxLength(15)
                        ->columnSpan(1),

                    Forms\Components\Select::make('estado')
                        ->options([
                            'activo'    => 'Activo',
                            'inactivo'  => 'Inactivo',
                            'bloqueado' => 'Bloqueado',
                        ])
                        ->default('activo')
                        ->required()
                        ->columnSpan(1),
                ]),

            Forms\Components\Section::make('Contacto')
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

                    Forms\Components\TextInput::make('pais')
                        ->label('País')
                        ->default('El Salvador')
                        ->maxLength(80)
                        ->columnSpan(1),
                ]),

            Forms\Components\Section::make('Condiciones Comerciales')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('limite_credito')
                        ->label('Límite de Crédito ($)')
                        ->numeric()
                        ->prefix('$')
                        ->default(0)
                        ->step(0.01)
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('dias_credito')
                        ->label('Días de Crédito')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->columnSpan(1),

                    Forms\Components\Textarea::make('notas')
                        ->label('Observaciones')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(Cliente::query()) // Asegurar que la consulta sea correcta
            ->columns([
                Tables\Columns\TextColumn::make('codigo')
                    ->label('Código')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('nombre')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable()
                    ->description(fn($record) => $record->tipo_label),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('limite_credito')
                    ->label('Límite Crédito')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('dias_credito')
                    ->label('Días Crédito')
                    ->suffix(' días')
                    ->alignCenter()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('estado')
                    ->badge()
                    ->colors([
                        'success' => 'activo',
                        'gray'    => 'inactivo',
                        'danger'  => 'bloqueado',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->options([
                        'activo'    => 'Activo',
                        'inactivo'  => 'Inactivo',
                        'bloqueado' => 'Bloqueado',
                    ]),
                Tables\Filters\SelectFilter::make('tipo')
                    ->options([
                        'minorista'   => 'Minorista',
                        'mayorista'   => 'Mayorista',
                        'corporativo' => 'Corporativo',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('nuevo_pedido')
                    ->label('Nueva OV')
                    ->icon('heroicon-o-shopping-cart')
                    ->color('success')
                    ->url(fn($record) => route('filament.admin.resources.pedido-ventas.create') . '?cliente_id=' . $record->id),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('nombre');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCliente::route('/'),
            'create' => Pages\CreateCliente::route('/create'),
            'edit'   => Pages\EditCliente::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) Cliente::where('estado', 'activo')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }
}