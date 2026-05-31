<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AsientoContable extends Model
{
    protected $table = 'asientos_contables';

    protected $fillable = [
        'numero', 'fecha', 'descripcion', 'tipo_documento', 'numero_documento',
        'total_debe', 'total_haber', 'estado', 'origen_tipo', 'origen_id',
        'notas', 'user_id',
    ];

    protected $casts = [
        'fecha'       => 'date',
        'total_debe'  => 'decimal:2',
        'total_haber' => 'decimal:2',
    ];

    // ─── Relaciones ──────────────────────────────────────────────────────────

    public function lineas(): HasMany
    {
        return $this->hasMany(LineaAsiento::class)->orderBy('orden')->orderBy('id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ─── Accessors ───────────────────────────────────────────────────────────

    public function getEstaBalanceadoAttribute(): bool
    {
        return abs((float) $this->total_debe - (float) $this->total_haber) < 0.005;
    }

    public function getEstadoColorAttribute(): string
    {
        return match ($this->estado) {
            'registrado' => 'success',
            'borrador'   => 'warning',
            'anulado'    => 'danger',
            default      => 'gray',
        };
    }

    public function getTipoDocumentoLabelAttribute(): string
    {
        return match ($this->tipo_documento) {
            'factura_cf'          => 'Factura Consumidor Final',
            'ccf'                 => 'Comprobante de Crédito Fiscal',
            'nota_debito'         => 'Nota de Débito',
            'nota_credito'        => 'Nota de Crédito',
            'comprobante_interno' => 'Comprobante Interno',
            'transferencia'       => 'Transferencia Bancaria',
            default               => $this->tipo_documento,
        };
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public static function generarNumero(): string
    {
        $year   = now()->year;
        $ultimo = static::whereYear('created_at', $year)
            ->where('numero', 'like', "ASI-{$year}-%")
            ->orderByDesc('numero')
            ->value('numero');

        $secuencia = $ultimo ? ((int) substr($ultimo, -4)) + 1 : 1;

        return "ASI-{$year}-" . str_pad($secuencia, 4, '0', STR_PAD_LEFT);
    }

    public function recalcularTotales(): void
    {
        $this->total_debe  = $this->lineas()->sum('debe');
        $this->total_haber = $this->lineas()->sum('haber');
        $this->saveQuietly();
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopeRegistrados($query)
    {
        return $query->where('estado', 'registrado');
    }

    public function scopePeriodo($query, string $desde, string $hasta)
    {
        return $query->whereBetween('fecha', [$desde, $hasta]);
    }
}
