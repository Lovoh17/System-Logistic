<?php

namespace App\Filament\Pages;

use App\Models\Transportista;
use Filament\Pages\Page;

class MapaTransportistas extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-map';
    protected static ?string $navigationLabel = 'Mapa de Transportistas';
    protected static ?string $title = 'Ubicación en Tiempo Real';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.pages.mapa-transportistas';

    public static function getNavigationGroup(): ?string
    {
        return 'Logística';
    }

    public function getTransportistasProperty()
    {
        return Transportista::where('estado', '!=', 'inactivo')
            ->whereNotNull('latitud')
            ->whereNotNull('longitud')
            ->get();
    }

    public function getTodosTransportistasProperty()
    {
        return Transportista::where('estado', '!=', 'inactivo')->get();
    }

    public function getTransportistasDisponiblesProperty()
    {
        return Transportista::where('estado', 'disponible')->get();
    }

    public function getTransportistasEnRutaProperty()
    {
        return Transportista::where('estado', 'en_ruta')
            ->orderBy('ultima_ubicacion_at', 'desc')
            ->get();
    }

    public function getTransportistasMantenimientoProperty()
    {
        return Transportista::where('estado', 'mantenimiento')->get();
    }
}