<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Producto extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'productos';

    protected $fillable = [
        'codigo', 'sku', 'nombre', 'descripcion',
        'categoria_id', 'proveedor_id',
        'unidad_medida', 'precio_compra', 'precio_venta',
        'stock_actual', 'stock_minimo', 'stock_maximo',
        'peso_kg', 'ubicacion_almacen', 'imagen',
        'requiere_refrigeracion', 'es_perecedero', 'vida_util_dias',
        'estado',
    ];

    protected $casts = [
        'precio_compra'          => 'decimal:2',
        'precio_venta'           => 'decimal:2',
        'stock_actual'           => 'decimal:3',
        'stock_minimo'           => 'decimal:3',
        'stock_maximo'           => 'decimal:3',
        'peso_kg'                => 'decimal:3',
        'requiere_refrigeracion' => 'boolean',
        'es_perecedero'          => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty();
    }

    // Relaciones
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class);
    }
    public function inventarioPorAlmacen()
    {
        return $this->hasMany(InventarioAlmacen::class);
    }

    // Scopes
    public function scopeActivo($query)
    {
        return $query->where('estado', 'activo');
    }

    public function scopeStockBajo($query)
    {
        return $query->whereColumn('stock_actual', '<=', 'stock_minimo');
    }

    public function scopeSinStock($query)
    {
        return $query->where('stock_actual', '<=', 0);
    }

    // Computed
    public function getStockEstadoAttribute(): string
    {
        if ($this->stock_actual <= 0) return 'sin_stock';
        if ($this->stock_actual <= $this->stock_minimo) return 'bajo';
        if ($this->stock_actual >= $this->stock_maximo) return 'exceso';
        return 'normal';
    }

    public function getStockColorAttribute(): string
    {
        return match($this->stock_estado) {
            'sin_stock' => 'danger',
            'bajo'      => 'warning',
            'exceso'    => 'info',
            default     => 'success',
        };
    }

    public function getMargenAttribute(): float
    {
        if ($this->precio_compra == 0) return 0;
        return round((($this->precio_venta - $this->precio_compra) / $this->precio_compra) * 100, 2);
    }

    public static function generarCodigo(): string
    {
        $ultimo = static::withTrashed()->max('id') ?? 0;
        return 'PROD-' . str_pad($ultimo + 1, 6, '0', STR_PAD_LEFT);
    }
}
