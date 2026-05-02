<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Cliente extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'clientes';

    protected $fillable = [
        'codigo', 'nombre', 'razon_social', 'nit', 'dui',
        'email', 'telefono', 'celular',
        'pais', 'departamento', 'municipio', 'direccion_principal',
        'tipo', 'limite_credito', 'dias_credito',
        'estado', 'notas',
    ];

    protected $casts = [
        'limite_credito' => 'decimal:2',
        'dias_credito'   => 'integer',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty();
    }

    public function pedidosVenta(): HasMany
    {
        return $this->hasMany(PedidoVenta::class);
    }

    public function scopeActivo($query)
    {
        return $query->where('estado', 'activo');
    }

    public function getEstadoColorAttribute(): string
    {
        return match($this->estado) {
            'activo'   => 'success',
            'inactivo' => 'gray',
            'bloqueado' => 'danger',
            default    => 'gray',
        };
    }

    public function getTipoLabelAttribute(): string
    {
        return match($this->tipo) {
            'minorista'   => 'Minorista',
            'mayorista'   => 'Mayorista',
            'corporativo' => 'Corporativo',
            default       => ucfirst($this->tipo),
        };
    }

    public static function generarCodigo(): string
    {
        $ultimo = static::withTrashed()->max('id') ?? 0;
        return 'CLI-' . str_pad($ultimo + 1, 5, '0', STR_PAD_LEFT);
    }
}
