<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PedidoCompra extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'pedidos_compra';

    protected $fillable = [
        'numero', 'proveedor_id', 'user_id',
        'fecha_pedido', 'fecha_requerida', 'fecha_recepcion',
        'estado', 'subtotal', 'impuesto', 'descuento', 'total',
        'moneda', 'condiciones_pago', 'notas', 'motivo_cancelacion',
    ];

    protected $casts = [
        'fecha_pedido'    => 'date',
        'fecha_requerida' => 'date',
        'fecha_recepcion' => 'date',
        'subtotal'        => 'decimal:2',
        'impuesto'        => 'decimal:2',
        'descuento'       => 'decimal:2',
        'total'           => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty();
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PedidoCompraItem::class);
    }

    public function getEstadoColorAttribute(): string
    {
        return match($this->estado) {
            'borrador'   => 'gray',
            'enviado'    => 'info',
            'confirmado' => 'primary',
            'parcial'    => 'warning',
            'recibido'   => 'success',
            'cancelado'  => 'danger',
            default      => 'gray',
        };
    }

    public function getEstadoLabelAttribute(): string
    {
        return match($this->estado) {
            'borrador'   => 'Borrador',
            'enviado'    => 'Enviado',
            'confirmado' => 'Confirmado',
            'parcial'    => 'Parcialmente Recibido',
            'recibido'   => 'Recibido',
            'cancelado'  => 'Cancelado',
            default      => ucfirst($this->estado),
        };
    }

    public static function generarNumero(): string
    {
        $anio = date('Y');
        $mes  = date('m');
        $ultimo = static::whereYear('created_at', $anio)->whereMonth('created_at', $mes)->count();
        return "OC-{$anio}{$mes}-" . str_pad($ultimo + 1, 4, '0', STR_PAD_LEFT);
    }
}
