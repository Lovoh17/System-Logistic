<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transportista extends Model
{
    use SoftDeletes;

    protected $table = 'transportistas';

    protected $fillable = [
        'user_id', 'almacen_id', 'codigo',
        'vehiculo_tipo', 'vehiculo_placa', 'vehiculo_modelo',
        'capacidad_kg', 'capacidad_m3',
        'tiene_refrigeracion', 'tiene_gps',
        'estado',
        'latitud', 'longitud', 'ubicacion_actual', 'ultima_ubicacion_at',
    ];

    protected $casts = [
        'capacidad_kg'        => 'decimal:2',
        'capacidad_m3'        => 'decimal:2',
        'tiene_refrigeracion' => 'boolean',
        'tiene_gps'           => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class);
    }

    public function envios(): HasMany
    {
        return $this->hasMany(Envio::class);
    }

    public function getEstadoColorAttribute(): string
    {
        return match($this->estado) {
            'disponible'    => 'success',
            'en_ruta'       => 'warning',
            'mantenimiento' => 'danger',
            'inactivo'      => 'gray',
            default         => 'gray',
        };
    }

    public static function generarCodigo(): string
    {
        $ultimo = static::withTrashed()->max('id') ?? 0;
        return 'TRANS-' . str_pad($ultimo + 1, 4, '0', STR_PAD_LEFT);
    }
}
