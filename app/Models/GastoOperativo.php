<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class GastoOperativo extends Model
{
    const FORMA_PAGO = ['efectivo', 'transferencia', 'tarjeta', 'otro'];

    protected $table = 'gastos_operativos';

    protected $fillable = [
        'concepto_gasto',
        'categoria_gasto_id',
        'monto',
        'fecha_gasto',
        'cliente_id',
        'comprobante',
        'forma_pago',
        'observaciones',
        'registrado_por',
    ];

    protected function casts(): array
    {
        return [
            'fecha_gasto' => 'date',
            'monto' => 'decimal:2',
        ];
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaGasto::class, 'categoria_gasto_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    public function scopeGenerales($query)
    {
        return $query->whereNull('cliente_id');
    }

    public function scopeDelCliente($query, int $clienteId)
    {
        return $query->where('cliente_id', $clienteId);
    }

    public function getComprobanteUrlAttribute(): ?string
    {
        return $this->comprobante ? Storage::disk('public')->url($this->comprobante) : null;
    }
}
