<?php
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
