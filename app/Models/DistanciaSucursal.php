<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistanciaSucursal extends Model
{
    protected $table = 'distancias_sucursales';

    protected $fillable = ['almacen1_id', 'almacen2_id', 'distancia_km'];

    protected $casts = ['distancia_km' => 'decimal:2'];

    public function almacen1(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacen1_id');
    }

    public function almacen2(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacen2_id');
    }
}
