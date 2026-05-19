<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DireccionCliente extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'direcciones_clientes';

    protected $fillable = [
        'cliente_id',
        'alias',
        'destinatario',
        'telefono',
        'pais',
        'departamento',
        'municipio',
        'direccion',
        'referencia',
        'es_principal',
        'activo',
    ];

    protected $casts = [
        'es_principal' => 'boolean',
        'activo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relación con el cliente
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    // Scope para direcciones principales
    public function scopePrincipal($query)
    {
        return $query->where('es_principal', true);
    }

    // Scope para direcciones activas
    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    // Accesor para obtener la dirección completa formateada
    public function getDireccionCompletaAttribute()
    {
        $partes = [];
        
        if ($this->direccion) {
            $partes[] = $this->direccion;
        }
        
        if ($this->municipio) {
            $partes[] = $this->municipio;
        }
        
        if ($this->departamento) {
            $partes[] = $this->departamento;
        }
        
        if ($this->pais) {
            $partes[] = $this->pais;
        }
        
        return implode(', ', $partes);
    }

    // Accesor para ver si es la dirección principal
    public function getEsPrincipalTextoAttribute()
    {
        return $this->es_principal ? 'Sí' : 'No';
    }

    // Mutador para asegurar que solo haya una dirección principal por cliente
    public function setEsPrincipalAttribute($value)
    {
        if ($value && $this->cliente_id) {
            // Si esta dirección se marca como principal,
            // desmarcar las otras direcciones del mismo cliente
            static::where('cliente_id', $this->cliente_id)
                ->where('id', '!=', $this->id ?? 0)
                ->update(['es_principal' => false]);
        }
        
        $this->attributes['es_principal'] = $value;
    }
}