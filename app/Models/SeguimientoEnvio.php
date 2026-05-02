<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeguimientoEnvio extends Model
{
    protected $table = 'seguimiento_envios';

    protected $fillable = [
        'envio_id', 'evento', 'descripcion', 'ubicacion',
        'latitud', 'longitud', 'responsable', 'fecha_hora',
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
        'latitud'    => 'decimal:8',
        'longitud'   => 'decimal:8',
    ];

    public function envio(): BelongsTo
    {
        return $this->belongsTo(Envio::class);
    }
}
