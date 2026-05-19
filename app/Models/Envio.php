<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Envio extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'envios';

    protected $fillable = [
        'numero',
        'pedido_venta_id',
        'transportista_id',
        'user_id',
        'fecha_programada',
        'fecha_salida',
        'fecha_entrega_estimada',
        'fecha_entrega_real',
        'estado',
        'origen_nombre',
        'origen_direccion',
        'destino_nombre',
        'destino_direccion',
        'destino_departamento',
        'destino_municipio',
        'distancia_km',
        'peso_total_kg',
        'volumen_total_m3',
        'costo_envio',
        'numero_seguimiento',
        'latitud_actual',
        'longitud_actual',
        'firma_receptor',
        'foto_entrega',
        'observaciones',
        'motivo_fallo',
    ];

    protected $casts = [
        'fecha_programada' => 'date',
        'fecha_salida' => 'datetime',
        'fecha_entrega_estimada' => 'datetime',
        'fecha_entrega_real' => 'datetime',
        'peso_total_kg' => 'decimal:3',
        'volumen_total_m3' => 'decimal:3',
        'distancia_km' => 'decimal:2',
        'costo_envio' => 'decimal:2',
        'latitud_actual' => 'decimal:8',
        'longitud_actual' => 'decimal:8',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty();
    }
    public function pedidoVenta(): BelongsTo
    {
        return $this->belongsTo(PedidoVenta::class);
    }

    public function transportista(): BelongsTo
    {
        return $this->belongsTo(Transportista::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function seguimientos(): HasMany
    {
        return $this->hasMany(SeguimientoEnvio::class);
    }

    public function getEstadoColorAttribute(): string
    {
        return match($this->estado) {
            'programado'     => 'gray',
            'en_preparacion' => 'warning',
            'despachado'     => 'info',
            'en_transito'    => 'primary',
            'en_destino'     => 'indigo',
            'entregado'      => 'success',
            'fallido'        => 'danger',
            'devuelto'       => 'orange',
            default          => 'gray',
        };
    }

    public static function generarNumero(): string
    {
        $anio = date('Y');
        $mes = date('m');
        
        $ultimo = static::whereYear('created_at', $anio)
            ->whereMonth('created_at', $mes)
            ->orderBy('id', 'desc')
            ->first();
        
        if ($ultimo && $ultimo->numero) {
            $partes = explode('-', $ultimo->numero);
            $ultimoNumero = intval(end($partes));
            $nuevoNumero = $ultimoNumero + 1;
        } else {
            $nuevoNumero = 1;
        }
        
        return "ENV-{$anio}{$mes}-" . str_pad($nuevoNumero, 4, '0', STR_PAD_LEFT);
    }
}