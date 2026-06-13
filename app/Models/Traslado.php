<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Traslado extends Model
{
    protected $table = 'traslados';

    protected $fillable = [
        'numero',
        'almacen_origen_id',
        'almacen_destino_id',
        'transportista_id',
        'estado',
        'motivo',
        'creado_por',
        'aprobado_por',
        'fecha_aprobacion',
        'fecha_completado',
    ];

    protected $casts = [
        'fecha_aprobacion' => 'datetime',
        'fecha_completado' => 'datetime',
    ];

    // Relaciones
    public function almacenOrigen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacen_origen_id');
    }

    public function almacenDestino(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacen_destino_id');
    }

    public function transportista(): BelongsTo
    {
        return $this->belongsTo(Transportista::class);
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function aprobadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprobado_por');
    }

    public function items(): HasMany
    {
        return $this->hasMany(TrasladoItem::class);
    }

    public static function generarNumero(): string
    {
        $ultimo = self::orderBy('id', 'desc')->first();
        $numero = $ultimo ? intval(substr($ultimo->numero, -6)) + 1 : 1;

        return 'TRA-'.date('Ymd').'-'.str_pad($numero, 6, '0', STR_PAD_LEFT);
    }
}
