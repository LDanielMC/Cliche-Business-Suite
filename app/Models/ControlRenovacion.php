<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ControlRenovacion extends Model
{
    const ESTADO_VIGENTE = 'vigente';
    const ESTADO_POR_VENCER = 'por_vencer';
    const ESTADO_VENCIDO = 'vencido';
    const ESTADO_RENOVADO = 'renovado';

    protected $table = 'control_renovaciones';

    protected $fillable = [
        'cliente_id',
        'fecha_inicio',
        'fecha_vencimiento',
        'duracion_meses',
        'estado',
        'fecha_renovacion',
        'notificado_at',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_vencimiento' => 'date',
            'fecha_renovacion' => 'date',
            'notificado_at' => 'datetime',
            'duracion_meses' => 'integer',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }
}
