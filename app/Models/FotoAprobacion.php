<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FotoAprobacion extends Model
{
    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_SELECCIONADA = 'seleccionada';
    const ESTADO_APROBADA = 'aprobada';
    const ESTADO_DESCARTADA = 'descartada';

    protected $table = 'fotos_aprobacion';

    protected $fillable = [
        'paquete_aprobacion_id',
        'ruta_imagen',
        'estado',
        'orden',
    ];

    public function paquete(): BelongsTo
    {
        return $this->belongsTo(PaqueteAprobacion::class, 'paquete_aprobacion_id');
    }

    public function getUrlAttribute(): string
    {
        return \Illuminate\Support\Facades\Storage::disk('public')->url($this->ruta_imagen);
    }
}
