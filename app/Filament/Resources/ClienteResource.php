<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClienteResource\Pages;
use App\Models\Cliente;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ClienteResource extends Resource
{
    protected static ?string $model = Cliente::class;

    protected static ?string $navigationIcon  = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Clientes';
    protected static ?string $navigationGroup = 'Gestión de Socios';
    protected static ?int    $navigationSort  = 2;
    protected static ?string $modelLabel      = 'Cliente';
    protected static ?string $pluralModelLabel = 'Clientes';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Datos del Cliente')
                ->icon('heroicon-o-user-circle')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('codigo')
                        ->label('Código')
                        ->default(fn() => Cliente::generarCodigo())
                        ->disabled()->dehydrated()->required()
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('nombre')
                        ->label('Nombre / Razón Comercial')
                        ->required()->maxLength(150)
                        ->columnSpan(2),

                    Forms\Components\TextInput::make('razon_social')
                        ->label('Razón Social Legal')
                        ->maxLength(200)->columnSpan(2),

                    Forms\Components\Select::make('tipo')
                        ->label('Tipo de Cliente')
                        ->options([
                            'minorista'   => '🛍️ Minorista',
                            'mayorista'   => '🏭 Mayorista',
                            'corporativo' => '🏢 Corporativo',
                        ])
                        ->default('minorista')->required()->columnSpan(1),

                    Forms\Components\TextInput::make('nit')
                        ->label('NIT')->maxLength(20)->columnSpan(1),

                    Forms\Components\TextInput::make('dui')
                        ->label('DUI')->maxLength(15)->columnSpan(1),

                    Forms\Components\Select::make('estado')
                        ->options([
                            'activo'   => 'Activo',
                            'inactivo' => 'Inactivo',
                            'bloqueado' => 'Bloqueado',
                        ])
                        ->default('activo')->required()->columnSpan(1),
                ]),

            Forms\Components\Section::make('Contacto')
                ->icon('heroicon-o-phone')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('email')
                        ->label('Correo Electrónico')
                        ->email()->maxLength(150)->columnSpan(1),

                    Forms\Components\TextInput::make('telefono')
                        ->label('Teléfono')->tel()->maxLength(20)->columnSpan(1),

                    Forms\Components\TextInput::make('celular')
                        ->label('Celular / WhatsApp')->maxLength(20)->columnSpan(1),
                ]),

            Forms\Components\Section::make('Dirección Principal')
                ->icon('heroicon-o-map-pin')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('pais')
                        ->default('El Salvador')->maxLength(80)->columnSpan(1),

                    Forms\Components\Select::make('departamento')
                        ->label('Departamento')
                        ->options([
                            'Ahuachapán'     => 'Ahuachapán',
                            'Cabañas'        => 'Cabañas',
                            'Chalatenango'   => 'Chalatenango',
                            'Cuscatlán'      => 'Cuscatlán',
                            'La Libertad'    => 'La Libertad',
                            'La Paz'         => 'La Paz',
                            'La Unión'       => 'La Unión',
                            'Morazán'        => 'Morazán',
                            'San Miguel'     => 'San Miguel',
                            'San Salvador'   => 'San Salvador',
                            'San Vicente'    => 'San Vicente',
                            'Santa Ana'      => 'Santa Ana',
                            'Sonsonate'      => 'Sonsonate',
                            'Usulután'       => 'Usulután',
                        ])
                        ->searchable()->columnSpan(1),

                    Forms\Components\TextInput::make('municipio')
                        ->maxLength(80)->columnSpan(1),

                    Forms\Components\Textarea::make('direccion_principal')
                        ->label('Dirección')
                        ->rows(2)->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Condiciones Comerciales')
                ->icon('heroicon-o-banknotes')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('limite_credito')
                        ->label('Límite de Crédito ($)')
                        ->numeric()->prefix('$')->default(0)->step(0.01)->columnSpan(1),

                    Forms\Components\TextInput::make('dias_credito')
                        ->label('Días de Crédito')
                        ->numeric()->default(0)->minValue(0)->columnSpan(1),

                    Forms\Components\Textarea::make('notas')
                        ->label('Observaciones')
                        ->rows(3)->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('codigo')
                    ->label('Código')
                    ->searchable()->sortable()
                    ->badge()->color('gray'),

                Tables\Columns\TextColumn::make('nombre')
                    ->label('Cliente')
                    ->searchable()->sortable()
                    ->description(fn($record) => $record->tipo_label),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()->toggleable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->searchable()->toggleable(),

                Tables\Columns\TextColumn::make('departamento')
                    ->label('Departamento')
                    ->badge()->color('gray')->toggleable(),

                Tables\Columns\TextColumn::make('limite_credito')
                    ->label('Límite Crédito')
                    ->money('USD')->sortable()->toggleable(),

                Tables\Columns\TextColumn::make('dias_credito')
                    ->label('Días Crédito')
                    ->suffix(' días')->alignCenter()->toggleable(),

                Tables\Columns\TextColumn::make('pedidos_venta_count')
                    ->label('Pedidos')
                    ->counts('pedidosVenta')
                    ->badge()->color('info')->alignCenter(),

                Tables\Columns\BadgeColumn::make('estado')
                    ->colors([
                        'success' => 'activo',
                        'gray'    => 'inactivo',
                        'danger'  => 'bloqueado',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->options([
                        'activo'   => 'Activo',
                        'inactivo' => 'Inactivo',
                        'bloqueado' => 'Bloqueado',
                    ]),
                Tables\Filters\SelectFilter::make('tipo')
                    ->options([
                        'minorista'   => 'Minorista',
                        'mayorista'   => 'Mayorista',
                        'corporativo' => 'Corporativo',
                    ]),
                Tables\Filters\SelectFilter::make('departamento')
                    ->options([
                        'San Salvador' => 'San Salvador',
                        'Santa Ana'    => 'Santa Ana',
                        'La Libertad'  => 'La Libertad',
                        'San Miguel'   => 'San Miguel',
                        'Sonsonate'    => 'Sonsonate',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Datos del Cliente')
                ->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('codigo')->badge()->color('gray'),
                    Infolists\Components\TextEntry::make('nombre')->weight('bold'),
                    Infolists\Components\TextEntry::make('tipo_label')->label('Tipo'),
                    Infolists\Components\TextEntry::make('nit')->label('NIT'),
                    Infolists\Components\TextEntry::make('estado')
                        ->badge()->color(fn($record) => $record->estado_color),
                    Infolists\Components\TextEntry::make('departamento'),
                ]),
            Infolists\Components\Section::make('Contacto & Crédito')
                ->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('email')->copyable(),
                    Infolists\Components\TextEntry::make('telefono'),
                    Infolists\Components\TextEntry::make('celular'),
                    Infolists\Components\TextEntry::make('limite_credito')
                        ->label('Límite de Crédito')->money('USD'),
                    Infolists\Components\TextEntry::make('dias_credito')
                        ->label('Días de Crédito')->suffix(' días'),
                    Infolists\Components\TextEntry::make('direccion_principal')
                        ->label('Dirección')->columnSpanFull(),
                ]),
        ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('estado', 'activo')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCliente::route('/'),
            'create' => Pages\CreateCliente::route('/create'),
            'view'   => Pages\ViewCliente::route('/{record}'),
            'edit'   => Pages\EditCliente::route('/{record}/edit'),
        ];
    }
}
