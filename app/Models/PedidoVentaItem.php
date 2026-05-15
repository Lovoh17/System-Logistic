<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PedidoVentaItem extends Model
{

    protected $table = 'pedidos_venta_items';

    protected $fillable = [
        'pedido_venta_id',
        'producto_id',
        'cantidad',
        'cantidad_despachada',
        'precio_unitario',
        'descuento',
        'subtotal',
        'unidad_medida',
        'notas',
    ];

    protected $casts = [
        'cantidad' => 'decimal:3',
        'cantidad_despachada' => 'decimal:3',
        'precio_unitario' => 'decimal:2',
        'descuento' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty();
    }

    // Relaciones
    public function pedidoVenta(): BelongsTo
    {
        return $this->belongsTo(PedidoVenta::class, 'pedido_venta_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    // Accessors
    public function getCantidadRestanteAttribute(): float
    {
        return max(0, $this->cantidad - $this->cantidad_despachada);
    }

    public function getPorcentajeDespachadoAttribute(): float
    {
        if ($this->cantidad <= 0) return 0;
        return round(($this->cantidad_despachada / $this->cantidad) * 100, 2);
    }

    public function getSubtotalFormattedAttribute(): string
    {
        return '$' . number_format($this->subtotal, 2);
    }

    public function getPrecioUnitarioFormattedAttribute(): string
    {
        return '$' . number_format($this->precio_unitario, 2);
    }
}