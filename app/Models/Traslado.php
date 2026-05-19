<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Traslado extends Model
{

    protected $table = 'traslados';

    protected $fillable = [
        'numero',
        'producto_id',
        'almacen_origen_id',
        'almacen_destino_id',
        'transportista_id',
        'cantidad',
        'cantidad_recibida',
        'estado',
        'motivo',
        'fecha_programada',
        'fecha_salida',
        'fecha_entrega_estimada',
        'fecha_entrega_real',
        'observaciones',
        'creado_por',
        'asignado_por',
    ];

    protected $casts = [
        'cantidad' => 'decimal:3',
        'cantidad_recibida' => 'decimal:3',
        'fecha_programada' => 'date',
        'fecha_salida' => 'date',
        'fecha_entrega_estimada' => 'date',
        'fecha_entrega_real' => 'date',
    ];

    // Relaciones
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

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

    public function asignadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asignado_por');
    }

    // Generar número de traslado
    public static function generarNumero(): string
    {
        $ultimo = self::orderBy('id', 'desc')->first(); // ✅ Quitar withTrashed()
        $numero = $ultimo ? intval(substr($ultimo->numero, -6)) + 1 : 1;
        return 'TRA-' . date('Ymd') . '-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
    }
}