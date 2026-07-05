<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CalendarioFoto extends Model
{
    const ESTATUS_PROGRAMADA = 'programada';
    const ESTATUS_PUBLICADA = 'publicada';
    const ESTATUS_CANCELADA = 'cancelada';

    const ESTATUS = [
        self::ESTATUS_PROGRAMADA,
        self::ESTATUS_PUBLICADA,
        self::ESTATUS_CANCELADA,
    ];

    protected $fillable = [
        'cliente_id',
        'fotografia_asociada',
        'fecha_publicacion_programada',
        'estatus',
        'observaciones',
        'creado_por',
    ];

    protected function casts(): array
    {
        return [
            'fecha_publicacion_programada' => 'datetime',
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

    public function getFotografiaUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->fotografia_asociada);
    }
}
