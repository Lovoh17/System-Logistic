<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PedidoCompraItem extends Model
{

    protected $table = 'pedidos_compra_items';

    protected $fillable = [
        'pedido_compra_id',
        'producto_id',
        'cantidad',
        'cantidad_recibida',
        'precio_unitario',
        'descuento',
        'subtotal',
        'unidad_medida',
        'notas',
    ];

    protected $casts = [
        'cantidad' => 'decimal:3',
        'cantidad_recibida' => 'decimal:3',
        'precio_unitario' => 'decimal:2',
        'descuento' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function pedidoCompra(): BelongsTo
    {
        return $this->belongsTo(PedidoCompra::class, 'pedido_compra_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function getCantidadPendienteAttribute(): float
    {
        return max(0, $this->cantidad - $this->cantidad_recibida);
    }
}