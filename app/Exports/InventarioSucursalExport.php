<?php

namespace App\Exports;

use App\Models\Almacen;
use App\Models\InventarioAlmacen;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class InventarioSucursalExport implements WithMultipleSheets
{
    protected $sucursalId;

    public function __construct($sucursalId = null)
    {
        $this->sucursalId = $sucursalId;
    }

    public function sheets(): array
    {
        $sheets = [];
        
        // Si se especifica una sucursal, solo exporta esa
        if ($this->sucursalId) {
            $sucursal = Almacen::find($this->sucursalId);
            if ($sucursal) {
                $sheets[] = new InventarioSucursalSheet($sucursal);
            }
        } else {
            // Exportar todas las sucursales (una hoja por cada una)
            $sucursales = Almacen::where('activo', true)->get();
            foreach ($sucursales as $sucursal) {
                $sheets[] = new InventarioSucursalSheet($sucursal);
            }
        }
        
        return $sheets;
    }
}