<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ControlRenovacion extends Model
{
    const ESTATUS_VIGENTE = 'vigente';
    const ESTATUS_POR_VENCER = 'por_vencer';
    const ESTATUS_VENCIDO = 'vencido';

    const ESTATUS = [self::ESTATUS_VIGENTE, self::ESTATUS_POR_VENCER, self::ESTATUS_VENCIDO];

    protected $table = 'control_renovaciones';

    protected $fillable = [
        'cliente_id',
        'fecha_inicio',
        'fecha_vencimiento',
        'estatus',
        'fecha_recordatorio',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_vencimiento' => 'date',
            'fecha_recordatorio' => 'date',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }
}
