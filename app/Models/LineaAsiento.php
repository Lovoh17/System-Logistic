<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LineaAsiento extends Model
{
    protected $table = 'lineas_asiento';

    protected $fillable = [
        'asiento_contable_id', 'cuenta_contable_id',
        'descripcion', 'debe', 'haber', 'orden',
    ];

    protected $casts = [
        'debe'  => 'decimal:2',
        'haber' => 'decimal:2',
    ];

    public function asiento(): BelongsTo
    {
        return $this->belongsTo(AsientoContable::class, 'asiento_contable_id');
    }

    public function cuenta(): BelongsTo
    {
        return $this->belongsTo(CuentaContable::class, 'cuenta_contable_id');
    }
}
