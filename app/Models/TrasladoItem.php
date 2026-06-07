<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrasladoItem extends Model
{
    protected $table = 'traslados_items';

    protected $fillable = [
        'traslado_id',
        'producto_id',
        'cantidad_sugerida',
        'cantidad_real',
        'lote',
        'fecha_vencimiento',
        'notas',
    ];

    protected $casts = [
        'cantidad_sugerida' => 'decimal:3',
        'cantidad_real'     => 'decimal:3',
        'fecha_vencimiento' => 'date',
    ];

    public function traslado(): BelongsTo
    {
        return $this->belongsTo(Traslado::class);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }
}
