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
        'ruta_foto',
        'estatus',
        'comentarios',
    ];

    public function paquete(): BelongsTo
    {
        return $this->belongsTo(PaqueteAprobacion::class, 'paquete_aprobacion_id');
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->ruta_foto);
    }
}
