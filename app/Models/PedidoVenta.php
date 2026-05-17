<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PedidoVenta extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'pedidos_venta';

    protected $fillable = [
        'numero', 'cliente_id', 'user_id', 'almacen_id',
        'fecha_pedido', 'fecha_requerida', 'fecha_entrega_real',
        'estado', 'prioridad',
        'subtotal', 'impuesto', 'descuento', 'costo_envio', 'total',
        'moneda', 'direccion_entrega', 'departamento_entrega', 'municipio_entrega',
        'instrucciones_entrega', 'canal_venta', 'notas',
    ];

    protected $casts = [
        'fecha_pedido'       => 'date',
        'fecha_requerida'    => 'date',
        'fecha_entrega_real' => 'date',
        'subtotal'           => 'decimal:2',
        'impuesto'           => 'decimal:2',
        'descuento'          => 'decimal:2',
        'costo_envio'        => 'decimal:2',
        'total'              => 'decimal:2',
    ];

    protected static function booted()
    {
        static::creating(function ($pedido) {
            if (!$pedido->user_id) {
                $pedido->user_id = auth()->id();
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty();
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function almacen()
    {
        return $this->belongsTo(Almacen::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PedidoVentaItem::class);
    }

    public function envios(): HasMany
    {
        return $this->hasMany(Envio::class);
    }

    public function getEstadoColorAttribute(): string
    {
        return match($this->estado) {
            'borrador'        => 'gray',
            'confirmado'      => 'info',
            'en_preparacion'  => 'warning',
            'listo'           => 'primary',
            'en_transito'     => 'indigo',
            'entregado'       => 'success',
            'cancelado'       => 'danger',
            'devolucion'      => 'orange',
            default           => 'gray',
        };
    }

    public function getPrioridadColorAttribute(): string
    {
        return match($this->prioridad) {
            'baja'    => 'gray',
            'normal'  => 'info',
            'alta'    => 'warning',
            'urgente' => 'danger',
            default   => 'gray',
        };
    }


    public static function generarNumero(): string
    {
        $anio = date('Y');
        $mes  = date('m');
        $prefijo = "OV-{$anio}{$mes}-";
        
        $ultimo = static::withTrashed() 
            ->where('numero', 'like', "{$prefijo}%")
            ->orderByDesc('numero')
            ->value('numero');
        
        if ($ultimo) {
            $ultimoNum = (int) substr($ultimo, strlen($prefijo));
            $siguiente = $ultimoNum + 1;
        } else {
            $siguiente = 1;
        }
        
        return $prefijo . str_pad($siguiente, 4, '0', STR_PAD_LEFT);
    }
}



