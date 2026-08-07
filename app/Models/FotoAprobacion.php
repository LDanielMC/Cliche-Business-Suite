<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class FotoAprobacion extends Model
{
    const ESTATUS_PENDIENTE = 'pendiente';
    const ESTATUS_APROBADA = 'aprobada';
    const ESTATUS_DESCARTADA = 'descartada';
    const ESTATUS_CONSERVADA = 'conservada';

    protected $table = 'fotos_aprobacion';

    protected $fillable = [
        'paquete_aprobacion_id',
        'cliente_id',
        'ruta_foto',
        'estatus',
        'prioridad',
        'comentarios',
        'fecha_ingreso_reserva',
        'fecha_expiracion_reserva',
        'veces_conservada',
    ];

    protected function casts(): array
    {
        return [
            'fecha_ingreso_reserva'    => 'date',
            'fecha_expiracion_reserva' => 'date',
        ];
    }

    public function paquete(): BelongsTo
    {
        return $this->belongsTo(PaqueteAprobacion::class, 'paquete_aprobacion_id');
    }

    /**
     * Referencia directa al cliente, independiente del paquete. Las fotos en
     * el Banco de Reserva (conservada) pueden quedar sin paquete_aprobacion_id
     * (para no perderse si ese paquete se borra) — esta relación es la única
     * forma confiable de saber de quién es una foto en ese estado.
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function getUrlAttribute(): string
    {
        // Las fotos nuevas van a GCS (ruta sin prefijo /storage/)
        // Las fotos legacy del disco local mantienen la URL anterior
        if (str_starts_with($this->ruta_foto, 'fotos-aprobacion/')) {
            return 'https://storage.googleapis.com/' . env('GCS_BUCKET', 'cliche-fotos-prod') . '/' . $this->ruta_foto;
        }

        return Storage::disk('public')->url($this->ruta_foto);
    }

    /**
     * Envía un lote de fotos a "conservada" (Banco de Reserva), llevando la
     * cuenta de cuántas veces le ha pasado a cada una. La que llegue al
     * límite configurado (renovaciones.max_veces_conservada) se marca
     * "descartada" en su lugar — corta el ciclo de reingresar → volver a
     * conservar → reingresar sin que nadie tome nunca una decisión final.
     *
     * @param array<int> $ids
     * @return \Illuminate\Support\Collection<int, FotoAprobacion> Las que llegaron al límite y quedaron descartadas.
     */
    public static function conservarConLimite(array $ids, string $fechaIngreso, string $fechaExpiracion): \Illuminate\Support\Collection
    {
        $limite = (int) config('renovaciones.max_veces_conservada', 3);
        $fotos  = static::whereIn('id', $ids)->get();

        $descartadasPorLimite = collect();

        foreach ($fotos as $foto) {
            $nuevoConteo = $foto->veces_conservada + 1;

            if ($nuevoConteo >= $limite) {
                $foto->update([
                    'estatus'          => self::ESTATUS_DESCARTADA,
                    'veces_conservada' => $nuevoConteo,
                ]);
                $descartadasPorLimite->push($foto);
            } else {
                $foto->update([
                    'estatus'                  => self::ESTATUS_CONSERVADA,
                    'veces_conservada'         => $nuevoConteo,
                    'fecha_ingreso_reserva'    => $fechaIngreso,
                    'fecha_expiracion_reserva' => $fechaExpiracion,
                ]);
            }
        }

        return $descartadasPorLimite;
    }
}
