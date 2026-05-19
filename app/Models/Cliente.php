<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'clientes';

    protected $fillable = [
        'codigo',
        'nombre',
        'razon_social',
        'nit',
        'dui',
        'email',
        'telefono',
        'celular',
        'pais',
        'tipo',
        'limite_credito',
        'dias_credito',
        'estado',
        'notas',
    ];

    protected $casts = [
        'limite_credito' => 'decimal:2',
        'dias_credito' => 'integer',
    ];

    // Relaciones
    public function direcciones()
    {
        return $this->hasMany(DireccionCliente::class);
    }

    public function direccionPrincipal()
    {
        return $this->hasOne(DireccionCliente::class)->where('es_principal', true)->where('activo', true);
    }

    public function pedidosVenta()
    {
        return $this->hasMany(PedidoVenta::class);
    }

    // Scopes
    public function scopeActivo($query)
    {
        return $query->where('estado', 'activo');
    }

    public function scopeMayorista($query)
    {
        return $query->where('tipo', 'mayorista');
    }

    // Accessors
    public function getTipoLabelAttribute()
    {
        return match($this->tipo) {
            'minorista'   => '🛍️ Minorista',
            'mayorista'   => '🏭 Mayorista',
            'corporativo' => '🏢 Corporativo',
            default       => $this->tipo,
        };
    }

    public function getEstadoColorAttribute()
    {
        return match($this->estado) {
            'activo'    => 'success',
            'inactivo'  => 'gray',
            'bloqueado' => 'danger',
            default     => 'gray',
        };
    }

    // ✅ Generador de código mejorado
    public static function generarCodigo()
    {
        $ultimo = self::orderBy('id', 'desc')->first();
        
        if (!$ultimo || !$ultimo->codigo) {
            return 'CLI-001';
        }
        
        // Extraer el número del código (ej: CLI-001 -> 1)
        preg_match('/(\d+)$/', $ultimo->codigo, $matches);
        $numero = isset($matches[1]) ? intval($matches[1]) + 1 : 1;
        
        return 'CLI-' . str_pad($numero, 3, '0', STR_PAD_LEFT);
    }
}