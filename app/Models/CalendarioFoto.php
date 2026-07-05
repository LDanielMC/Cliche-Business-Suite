<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarioFoto extends Model
{
    const ESTADO_PROGRAMADA = 'programada';
    const ESTADO_PUBLICADA = 'publicada';
    const ESTADO_CANCELADA = 'cancelada';

    const ESTADOS = [
        self::ESTADO_PROGRAMADA,
        self::ESTADO_PUBLICADA,
        self::ESTADO_CANCELADA,
    ];

    protected $fillable = [
        'cliente_id',
        'fecha_publicacion',
        'descripcion',
        'estado',
        'foto_aprobacion_id',
        'creado_por',
    ];

    protected function casts(): array
    {
        return [
            'fecha_publicacion' => 'date',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function fotoAprobacion(): BelongsTo
    {
        return $this->belongsTo(FotoAprobacion::class, 'foto_aprobacion_id');
    }
}
