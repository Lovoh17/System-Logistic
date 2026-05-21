<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Validation\ValidationException;

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
        'cantidad'          => 'integer',      // ← cambiado a integer
        'stock_anterior'    => 'decimal:3',
        'stock_nuevo'       => 'decimal:3',
        'costo_unitario'    => 'decimal:4',
        'costo_total'       => 'decimal:2',
        'fecha_vencimiento' => 'date',
        'fecha_movimiento'  => 'datetime',
    ];

    // ── Validación a nivel de modelo ─────────────────────
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model) {
            // Producto obligatorio
            if (empty($model->producto_id)) {
                throw ValidationException::withMessages([
                    'producto_id' => 'El producto es obligatorio.',
                ]);
            }

            // Tipo obligatorio
            if (empty($model->tipo)) {
                throw ValidationException::withMessages([
                    'tipo' => 'El tipo de movimiento es obligatorio.',
                ]);
            }

            // Cantidad: entero mayor a 0
            if (empty($model->cantidad) || !is_numeric($model->cantidad) || intval($model->cantidad) < 1 || $model->cantidad != intval($model->cantidad)) {
                throw ValidationException::withMessages([
                    'cantidad' => 'La cantidad debe ser un número entero mayor a 0.',
                ]);
            }

            // Costo unitario: no negativo
            if (!is_null($model->costo_unitario) && $model->costo_unitario < 0) {
                throw ValidationException::withMessages([
                    'costo_unitario' => 'El costo unitario no puede ser negativo.',
                ]);
            }

            // Motivo obligatorio y mínimo 10 caracteres
            if (empty($model->motivo) || strlen(trim($model->motivo)) < 10) {
                throw ValidationException::withMessages([
                    'motivo' => 'El motivo es obligatorio y debe tener al menos 10 caracteres.',
                ]);
            }

            // Forzar cantidad como entero
            $model->cantidad = intval($model->cantidad);
        });
    }

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
        return $this->belongsTo(\App\Models\User::class);
    }

    public function referencia(): MorphTo
    {
        return $this->morphTo();
    }

    // ── Accessors ─────────────────────────────────────────
    public function getTipoColorAttribute(): string
    {
        return match($this->tipo) {
            'entrada_compra', 'devolucion_venta',
            'ajuste_positivo', 'inventario_inicial',
            'traslado_entrada' => 'success',

            'salida_venta', 'devolucion_compra',
            'ajuste_negativo', 'traslado_salida',
            'merma'            => 'danger',

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
        return 'MOV-' . date('Ymd') . '-' . str_pad($ultimo + 1, 5, '0', STR_PAD_LEFT);
    }
}