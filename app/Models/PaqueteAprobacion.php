<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaqueteAprobacion extends Model
{
    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_COMPLETADO = 'completado';
    const ESTADO_AUTO_APROBADO = 'auto_aprobado';

    protected $table = 'paquetes_aprobacion';

    protected $fillable = [
        'cliente_id',
        'mes',
        'anio',
        'cantidad_requerida',
        'fecha_limite',
        'estado',
    ];

    protected function casts(): array
    {
        return [
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

    /**
     * Crea las entradas del calendario de fotos para cada fotografía
     * aprobada de este paquete, una por día a partir de la fecha límite.
     */
    public function publicarEnCalendario(?int $creadoPor = null): void
    {
        $aprobadas = $this->fotos()->where('estado', FotoAprobacion::ESTADO_APROBADA)->get();
        $fecha = $this->fecha_limite->copy();

        foreach ($aprobadas as $foto) {
            CalendarioFoto::create([
                'cliente_id' => $this->cliente_id,
                'fecha_publicacion' => $fecha->copy(),
                'descripcion' => 'Publicación aprobada — paquete ' . $this->mes . '/' . $this->anio,
                'estado' => CalendarioFoto::ESTADO_PROGRAMADA,
                'foto_aprobacion_id' => $foto->id,
                'creado_por' => $creadoPor ?? $this->cliente->user_id,
            ]);

            $fecha->addDay();
        }
    }
}
