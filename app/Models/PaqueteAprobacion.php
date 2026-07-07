<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaqueteAprobacion extends Model
{
    const ESTATUS_PENDIENTE = 'pendiente';
    const ESTATUS_COMPLETADO = 'completado';
    const ESTATUS_AUTO_APROBADO = 'auto_aprobado';

    protected $table = 'paquetes_aprobacion';

    protected $fillable = [
        'cliente_id',
        'mes_revision',
        'fecha_envio',
        'fecha_limite',
        'cantidad_requerida',
        'estatus',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_envio' => 'date',
            'fecha_limite' => 'date',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function fotos(): HasMany
    {
        return $this->hasMany(FotoAprobacion::class);
    }

    public function getMesRevisionLegibleAttribute(): string
    {
        return \Carbon\Carbon::createFromFormat('Y-m', $this->mes_revision)->translatedFormat('F Y');
    }

    /**
     * Crea las entradas del calendario de fotos para cada fotografía
     * aprobada de este paquete, una por día a partir de la fecha límite.
     */
    public function publicarEnCalendario(?int $creadoPor = null): void
    {
        $aprobadas = $this->fotos()->where('estatus', FotoAprobacion::ESTATUS_APROBADA)->get();
        $fecha = $this->fecha_limite->copy()->setTime(9, 0);

        foreach ($aprobadas as $foto) {
            CalendarioFoto::create([
                'cliente_id' => $this->cliente_id,
                'fotografia_asociada' => $foto->ruta_foto,
                'fecha_publicacion_programada' => $fecha->copy(),
                'estatus' => CalendarioFoto::ESTATUS_PROGRAMADA,
                'observaciones' => 'Publicación aprobada — paquete ' . $this->mes_revision_legible,
                'creado_por' => $creadoPor ?? $this->cliente->user_id,
            ]);

            $fecha->addDay();
        }
    }
}
