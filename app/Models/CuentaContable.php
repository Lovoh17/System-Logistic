<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CuentaContable extends Model
{
    protected $table = 'cuentas_contables';

    protected $fillable = [
        'codigo', 'nombre', 'tipo', 'naturaleza', 'nivel',
        'cuenta_padre_id', 'acepta_movimientos', 'activa', 'descripcion',
    ];

    protected $casts = [
        'acepta_movimientos' => 'boolean',
        'activa'             => 'boolean',
    ];

    // ─── Relaciones ──────────────────────────────────────────────────────────

    public function padre(): BelongsTo
    {
        return $this->belongsTo(CuentaContable::class, 'cuenta_padre_id');
    }

    public function hijos(): HasMany
    {
        return $this->hasMany(CuentaContable::class, 'cuenta_padre_id');
    }

    public function lineasAsiento(): HasMany
    {
        return $this->hasMany(LineaAsiento::class);
    }

    // ─── Accessors ───────────────────────────────────────────────────────────

    public function getSaldoAttribute(): float
    {
        $debe  = $this->lineasAsiento()
            ->whereHas('asiento', fn ($q) => $q->where('estado', 'registrado'))
            ->sum('debe');
        $haber = $this->lineasAsiento()
            ->whereHas('asiento', fn ($q) => $q->where('estado', 'registrado'))
            ->sum('haber');

        return $this->naturaleza === 'deudora'
            ? (float) $debe - (float) $haber
            : (float) $haber - (float) $debe;
    }

    public function getSaldoPeriodo(string $desde, string $hasta): float
    {
        $base  = $this->lineasAsiento()
            ->whereHas('asiento', fn ($q) => $q->where('estado', 'registrado')
                ->whereBetween('fecha', [$desde, $hasta]));

        $debe  = (clone $base)->sum('debe');
        $haber = (clone $base)->sum('haber');

        return $this->naturaleza === 'deudora'
            ? (float) $debe - (float) $haber
            : (float) $haber - (float) $debe;
    }

    public function getTipoLabelAttribute(): string
    {
        return match ($this->tipo) {
            'activo'  => 'Activo',
            'pasivo'  => 'Pasivo',
            'capital' => 'Capital',
            'ingreso' => 'Ingreso',
            'costo'   => 'Costo',
            'gasto'   => 'Gasto',
            default   => $this->tipo,
        };
    }

    public function getTipoColorAttribute(): string
    {
        return match ($this->tipo) {
            'activo'  => 'info',
            'pasivo'  => 'warning',
            'capital' => 'success',
            'ingreso' => 'success',
            'costo'   => 'danger',
            'gasto'   => 'danger',
            default   => 'gray',
        };
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopeMovibles($query)
    {
        return $query->where('acepta_movimientos', true)->where('activa', true);
    }

    public function scopeDeNivel($query, int $nivel)
    {
        return $query->where('nivel', $nivel);
    }
}
