<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClienteEstatusLog extends Model
{
    const EVENTO_ALTA         = 'alta';
    const EVENTO_BAJA         = 'baja';
    const EVENTO_SUSPENSION   = 'suspension';
    const EVENTO_REACTIVACION = 'reactivacion'; // Automatic: triggered by completed renovation
    const EVENTO_RESTAURACION = 'restauracion'; // Manual: admin restores a dado_de_baja client to suspendido

    protected $table = 'cliente_estatus_logs';

    protected $fillable = [
        'cliente_id',
        'user_id',
        'evento',
        'fecha_evento',
        'registrado_por',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_evento' => 'date',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class)->withDefault();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }
}
