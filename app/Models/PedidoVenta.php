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
        'numero', 'cliente_id', 'user_id',
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
        $ultimo = static::whereYear('created_at', $anio)->whereMonth('created_at', $mes)->count();
        return "OV-{$anio}{$mes}-" . str_pad($ultimo + 1, 4, '0', STR_PAD_LEFT);
    }
}

// ────────────────────────────────────────────────

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PedidoCompraItem extends Model
{
    protected $table = 'pedidos_compra_items';

    protected $fillable = [
        'pedido_compra_id', 'producto_id',
        'cantidad', 'cantidad_recibida', 'precio_unitario', 'descuento', 'subtotal',
        'unidad_medida', 'notas',
    ];

    protected $casts = [
        'cantidad'          => 'decimal:3',
        'cantidad_recibida' => 'decimal:3',
        'precio_unitario'   => 'decimal:2',
        'descuento'         => 'decimal:2',
        'subtotal'          => 'decimal:2',
    ];

    public function pedidoCompra(): BelongsTo
    {
        return $this->belongsTo(PedidoCompra::class);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }
}

// ────────────────────────────────────────────────

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PedidoVentaItem extends Model
{
    protected $table = 'pedidos_venta_items';

    protected $fillable = [
        'pedido_venta_id', 'producto_id',
        'cantidad', 'cantidad_despachada', 'precio_unitario', 'descuento', 'subtotal',
        'unidad_medida', 'notas',
    ];

    protected $casts = [
        'cantidad'            => 'decimal:3',
        'cantidad_despachada' => 'decimal:3',
        'precio_unitario'     => 'decimal:2',
        'descuento'           => 'decimal:2',
        'subtotal'            => 'decimal:2',
    ];

    public function pedidoVenta(): BelongsTo
    {
        return $this->belongsTo(PedidoVenta::class);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }
}
