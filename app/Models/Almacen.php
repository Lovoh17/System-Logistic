<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Almacen extends Model
{
    protected $table = 'almacenes';

    protected $fillable = [
        'codigo', 'nombre', 'direccion', 'responsable', 'telefono',
        'es_principal', 'activo', 'latitud', 'longitud',
    ];

    protected $casts = [
        'es_principal' => 'boolean',
        'activo'       => 'boolean',
        'latitud'      => 'decimal:7',
        'longitud'     => 'decimal:7',
    ];

    public function tieneCoordenadas(): bool
    {
        return !is_null($this->latitud) && !is_null($this->longitud);
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class);
    }
    public function inventario()
    {
        return $this->hasMany(InventarioAlmacen::class);
    }

    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }
}
