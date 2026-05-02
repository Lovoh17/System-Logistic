<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Categoria extends Model
{
    protected $table = 'categorias';

    protected $fillable = [
        'nombre', 'slug', 'descripcion', 'icono', 'color', 'parent_id', 'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class);
    }

    public function padre(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'parent_id');
    }

    public function hijos(): HasMany
    {
        return $this->hasMany(Categoria::class, 'parent_id');
    }

    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }
}
