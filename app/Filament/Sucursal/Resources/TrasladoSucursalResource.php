<?php

namespace App\Filament\Sucursal\Resources;

use App\Filament\Sucursal\Resources\TrasladoSucursalResource\Pages;
use App\Models\Almacen;
use App\Models\Producto;
use App\Models\Transportista;
use App\Models\Traslado;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TrasladoSucursalResource extends Resource
{
    protected static ?string $model = Traslado::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationLabel = 'Traslados';

    protected static ?string $navigationGroup = 'Traslados';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Traslado';

    protected static ?string $pluralModelLabel = 'Traslados';

    public static function getEloquentQuery(): Builder
    {
        $id = auth()->user()?->almacen_id;

        return parent::getEloquentQuery()
            ->where(fn ($q) => $q->where('almacen_origen_id', $id)->orWhere('almacen_destino_id', $id));
    }

    public static function form(Form $form): Form
    {
        $almacenId = auth()->user()?->almacen_id;

        return $form->schema([
            Forms\Components\Section::make('Datos del Traslado')->columns(3)->schema([
                Forms\Components\TextInput::make('numero')
                    ->label('N° Traslado')
                    ->default(fn () => Traslado::generarNumero())
                    ->disabled()->dehydrated()->required()->columnSpan(1),

                // El origen siempre es la sucursal del usuario (no editable).
                Forms\Components\Select::make('almacen_origen_id')
                    ->label('Sucursal Origen')
                    ->options(Almacen::where('id', $almacenId)->pluck('nombre', 'id'))
                    ->default($almacenId)
                    ->disabled()
                    ->dehydrated()
                    ->required()
                    ->columnSpan(1),

                Forms\Components\Select::make('almacen_destino_id')
                    ->label('Sucursal Destino')
                    ->options(Almacen::where('activo', true)
                        ->where('id', '!=', $almacenId)
                        ->pluck('nombre', 'id'))
                    ->required()->searchable()->columnSpan(1),

                Forms\Components\Select::make('transportista_id')
                    ->label('Transportista / Conductor')
                    ->options(function () use ($almacenId) {
                        return Transportista::with('user')
                            ->where('estado', 'disponible')
                            ->where('almacen_id', $almacenId)
                            ->get()
                            ->mapWithKeys(fn ($t) => [
                                $t->id => ($t->user?->name ?? '—').' — '.($t->vehiculo_placa ?? 'sin placa'),
                            ]);
                    })
                    ->searchable()
                    ->nullable()
                    ->helperText('Transportistas disponibles en tu sucursal.')
                    ->columnSpan(1),

                Forms\Components\Select::make('estado')
                    ->options(['sugerido' => 'Sugerido', 'aprobado' => 'Aprobado'])
                    ->default('sugerido')->required()->columnSpan(1),

                Forms\Components\Textarea::make('motivo')
                    ->label('Motivo')->rows(2)->required()->columnSpan(2),
            ]),

            Forms\Components\Section::make('Productos a Trasladar')->schema([
                Forms\Components\Repeater::make('items')
                    ->relationship()->label('')->columns(3)->defaultItems(1)
                    ->schema([
                        Forms\Components\Select::make('producto_id')
                            ->label('Producto')
                            ->options(Producto::activo()->pluck('nombre', 'id'))
                            ->required()->searchable()->columnSpan(1),

                        Forms\Components\TextInput::make('cantidad_sugerida')
                            ->label('Cantidad')->numeric()->required()->minValue(0.001)->step(0.001),

                        Forms\Components\Textarea::make('notas')
                            ->label('Notas')->rows(2),
                    ])->addActionLabel('+ Agregar Producto'),
            ]),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Traslado')->columns(4)->schema([
                Infolists\Components\TextEntry::make('numero')
                    ->label('N° Traslado')->badge()->color('primary'),
                Infolists\Components\TextEntry::make('estado')->badge()
                    ->color(fn ($state) => match ($state) {
                        'sugerido' => 'warning',
                        'aprobado' => 'info',
                        'en_transito' => 'primary',
                        'completado' => 'success',
                        'cancelado' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => ucfirst($state)),
                Infolists\Components\TextEntry::make('almacenOrigen.nombre')
                    ->label('Origen')->icon('heroicon-m-building-storefront'),
                Infolists\Components\TextEntry::make('almacenDestino.nombre')
                    ->label('Destino')->icon('heroicon-m-building-storefront'),
                Infolists\Components\TextEntry::make('transportista.user.name')
                    ->label('Conductor')
                    ->icon('heroicon-m-user')
                    ->placeholder('Sin asignar'),

                Infolists\Components\TextEntry::make('transportista.vehiculo_placa')
                    ->label('Placa / Modelo')
                    ->formatStateUsing(fn ($state, $record) => trim(($state ?? '—').' · '.($record->transportista?->vehiculo_modelo ?? ''))
                    )
                    ->placeholder('—'),
                Infolists\Components\TextEntry::make('motivo')->label('Motivo')->columnSpanFull(),
            ]),

            Infolists\Components\Section::make('Productos')->schema([
                Infolists\Components\RepeatableEntry::make('items')->label('')->columns(3)->schema([
                    Infolists\Components\TextEntry::make('producto.nombre')->label('Producto'),
                    Infolists\Components\TextEntry::make('cantidad_sugerida')->label('Cant. Sugerida')->numeric(3),
                    Infolists\Components\TextEntry::make('cantidad_real')->label('Cant. Real')->numeric(3)->placeholder('—'),
                    Infolists\Components\TextEntry::make('notas')->label('Notas')->placeholder('—'),
                ]),
            ]),

            Infolists\Components\Section::make('Fechas')->columns(3)->schema([
                Infolists\Components\TextEntry::make('created_at')->label('Creado')->dateTime('d/m/Y H:i'),
                Infolists\Components\TextEntry::make('fecha_aprobacion')->label('Aprobado')->dateTime('d/m/Y H:i')->placeholder('—'),
                Infolists\Components\TextEntry::make('fecha_completado')->label('Completado')->dateTime('d/m/Y H:i')->placeholder('—'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero')
                    ->label('N° Traslado')->searchable()->sortable()->badge()->color('primary'),

                Tables\Columns\TextColumn::make('almacenOrigen.nombre')
                    ->label('Origen')->badge()->color('gray'),

                Tables\Columns\TextColumn::make('almacenDestino.nombre')
                    ->label('Destino')->badge()->color('gray'),

                Tables\Columns\TextColumn::make('transportista.user.name')
                    ->label('Conductor')
                    ->searchable()
                    ->description(fn ($record) => $record->transportista?->vehiculo_placa)
                    ->placeholder('Sin asignar')
                    ->icon('heroicon-m-truck'),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('Productos')->counts('items')->badge()->color('primary')->alignCenter(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')->date('d/m/Y')->sortable(),

                Tables\Columns\TextColumn::make('estado')->badge()
                    ->color(fn ($state) => match ($state) {
                        'sugerido' => 'warning',
                        'aprobado' => 'info',
                        'en_transito' => 'primary',
                        'completado' => 'success',
                        'cancelado' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => ucfirst($state)),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')->options([
                    'sugerido' => 'Sugerido',
                    'aprobado' => 'Aprobado',
                    'en_transito' => 'En Tránsito',
                    'completado' => 'Completado',
                    'cancelado' => 'Cancelado',
                ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => $record->estado === 'sugerido'),
            ])
            ->headerActions([Tables\Actions\CreateAction::make()])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTrasladosSucursal::route('/'),
            'create' => Pages\CreateTrasladoSucursal::route('/create'),
            'view' => Pages\ViewTrasladoSucursal::route('/{record}'),
            'edit' => Pages\EditTrasladoSucursal::route('/{record}/edit'),
        ];
    }
}
