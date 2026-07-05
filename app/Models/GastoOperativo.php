<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GastoOperativo extends Model
{
    protected $table = 'gastos_operativos';

    protected $fillable = [
        'categoria_gasto_id',
        'cliente_id',
        'concepto',
        'monto',
        'fecha_gasto',
        'notas',
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
}
