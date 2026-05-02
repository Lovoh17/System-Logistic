<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Proveedor extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'proveedores';

    protected $fillable = [
        'codigo', 'nombre', 'razon_social', 'ruc', 'nit',
        'email', 'telefono', 'celular',
        'contacto_nombre', 'contacto_email', 'contacto_telefono',
        'pais', 'departamento', 'municipio', 'direccion',
        'categoria', 'tiempo_entrega_dias', 'calificacion',
        'estado', 'notas',
    ];

    protected $casts = [
        'calificacion' => 'decimal:2',
        'tiempo_entrega_dias' => 'integer',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty();
    }

    // Relaciones
    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class);
    }

    public function pedidosCompra(): HasMany
    {
        return $this->hasMany(PedidoCompra::class);
    }

    // Scopes
    public function scopeActivo($query)
    {
        return $query->where('estado', 'activo');
    }

    // Accessors
    public function getEstadoColorAttribute(): string
    {
        return match($this->estado) {
            'activo'    => 'success',
            'inactivo'  => 'gray',
            'suspendido' => 'danger',
            default     => 'gray',
        };
    }

    public function getCategoriaLabelAttribute(): string
    {
        return match($this->categoria) {
            'general'       => 'General',
            'materia_prima' => 'Materia Prima',
            'servicios'     => 'Servicios',
            default         => ucfirst($this->categoria),
        };
    }

    public static function generarCodigo(): string
    {
        $ultimo = static::withTrashed()->max('id') ?? 0;
        return 'PROV-' . str_pad($ultimo + 1, 5, '0', STR_PAD_LEFT);
    }
}
