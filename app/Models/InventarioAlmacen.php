<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventarioAlmacen extends Model
{
    protected $table = 'inventario_almacen';

    protected $fillable = [
        'producto_id',
        'almacen_id',
        'stock_actual',
        'stock_minimo',
        'stock_maximo',
        'punto_reorden',
    ];

    protected $casts = [
        'stock_actual' => 'decimal:3',
        'stock_minimo' => 'decimal:3',
        'stock_maximo' => 'decimal:3',
        'punto_reorden' => 'decimal:3',
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class);
    }
}