<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MovimientoInventario extends Model
{
    protected $table = 'movimientos_inventario';

    protected $fillable = [
        'numero', 'producto_id', 'almacen_id', 'user_id',
        'tipo', 'cantidad', 'stock_anterior', 'stock_nuevo',
        'costo_unitario', 'costo_total',
        'referencia_type', 'referencia_id',
        'lote', 'fecha_vencimiento', 'motivo', 'fecha_movimiento',
    ];

    protected $casts = [
        'cantidad' => 'decimal:3',
        'stock_anterior' => 'decimal:3',
        'stock_nuevo' => 'decimal:3',
        'costo_unitario' => 'decimal:4',
        'costo_total' => 'decimal:2',
        'fecha_vencimiento' => 'date',
        'fecha_movimiento' => 'datetime',
    ];

    // La validación de entrada manual vive en MovimientoInventarioResource (reglas
    // del formulario Filament). Las escrituras programáticas se hacen vía
    // InventoryService, que construye movimientos consistentes.

    // ── Relaciones ────────────────────────────────────────
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function referencia(): MorphTo
    {
        return $this->morphTo();
    }

    // ── Accessors ─────────────────────────────────────────
    public function getTipoColorAttribute(): string
    {
        return match ($this->tipo) {
            'entrada_compra', 'devolucion_venta',
            'ajuste_positivo', 'inventario_inicial',
            'traslado_entrada' => 'success',

            'salida_venta', 'devolucion_compra',
            'ajuste_negativo', 'traslado_salida',
            'merma' => 'danger',

            default => 'gray',
        };
    }

    public function getEsEntradaAttribute(): bool
    {
        return in_array($this->tipo, [
            'entrada_compra', 'devolucion_venta',
            'ajuste_positivo', 'inventario_inicial',
            'traslado_entrada',
        ]);
    }

    // ── Helpers ───────────────────────────────────────────
    public static function generarNumero(): string
    {
        $ultimo = static::max('id') ?? 0;

        return 'MOV-'.date('Ymd').'-'.str_pad($ultimo + 1, 5, '0', STR_PAD_LEFT);
    }
}
