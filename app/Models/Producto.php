<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'productos';

    protected $fillable = [
        'codigo',
        'sku',
        'nombre',
        'descripcion',
        'categoria_id',
        'proveedor_id',
        'unidad_medida',
        'precio_compra',
        'precio_venta',
        'peso_kg',
        'ubicacion_almacen',
        'imagen',
        'requiere_refrigeracion',
        'es_perecedero',
        'vida_util_dias',
        'estado',
    ];

    protected $casts = [
        'precio_compra' => 'decimal:2',
        'precio_venta' => 'decimal:2',
        'peso_kg' => 'decimal:3',
        'requiere_refrigeracion' => 'boolean',
        'es_perecedero' => 'boolean',
        'vida_util_dias' => 'integer',
    ];

    // Relaciones
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function inventarioAlmacen()
    {
        return $this->hasMany(InventarioAlmacen::class);
    }

    // Stock total = suma de stock_actual en todas las sucursales (inventario_almacen).
    // Usa la relación ya cargada si está disponible para evitar N+1.
    public function getStockTotalAttribute(): float
    {
        return $this->relationLoaded('inventarioAlmacen')
            ? (float) $this->inventarioAlmacen->sum('stock_actual')
            : (float) $this->inventarioAlmacen()->sum('stock_actual');
    }

    // Accessor para color del stock
    public function getStockColorAttribute(): string
    {
        $stock = $this->stock_total;

        if ($stock <= 0) {
            return 'danger';
        }
        if ($stock <= $this->stock_minimo_global) {
            return 'warning';
        }

        return 'success';
    }

    // Stock mínimo global = menor stock_minimo configurado entre sucursales.
    public function getStockMinimoGlobalAttribute(): float
    {
        return $this->relationLoaded('inventarioAlmacen')
            ? (float) ($this->inventarioAlmacen->min('stock_minimo') ?? 0)
            : (float) ($this->inventarioAlmacen()->min('stock_minimo') ?? 0);
    }

    // Scopes útiles
    public function scopeActivo($query)
    {
        return $query->where('estado', 'activo');
    }

    // Generador de código
    public static function generarCodigo()
    {
        $ultimo = self::orderBy('id', 'desc')->first();
        $numero = $ultimo ? intval(substr($ultimo->codigo, 5)) + 1 : 1;

        return 'PROD-'.str_pad($numero, 3, '0', STR_PAD_LEFT);
    }
}
