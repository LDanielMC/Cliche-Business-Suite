<?php

namespace App\Models;

use App\Models\FotoAprobacion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CalendarioFoto extends Model
{
    const ESTATUS_PROGRAMADA = 'programada';
    const ESTATUS_PUBLICADA  = 'publicada';
    const ESTATUS_CANCELADA  = 'cancelada';
    const ESTATUS_PAUSADA    = 'pausada'; // Client suspended; must be rescheduled when reactivated

    const ESTATUS = [
        self::ESTATUS_PROGRAMADA,
        self::ESTATUS_PUBLICADA,
        self::ESTATUS_CANCELADA,
        self::ESTATUS_PAUSADA,
    ];

    protected $fillable = [
        'cliente_id',
        'foto_aprobacion_id',
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

    public function fotoAprobacion(): BelongsTo
    {
        return $this->belongsTo(FotoAprobacion::class, 'foto_aprobacion_id');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function getFotografiaUrlAttribute(): string
    {
        // Mismo criterio que FotoAprobacion::getUrlAttribute(): fotos nuevas
        // viven en GCS, las legacy del disco local mantienen la URL anterior.
        if (str_starts_with($this->fotografia_asociada, 'fotos-aprobacion/')) {
            return 'https://storage.googleapis.com/' . env('GCS_BUCKET', 'cliche-fotos-prod') . '/' . $this->fotografia_asociada;
        }

        return Storage::disk('public')->url($this->fotografia_asociada);
    }
}
