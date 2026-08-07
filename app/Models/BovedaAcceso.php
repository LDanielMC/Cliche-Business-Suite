<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Bitácora de auditoría: quién reveló la contraseña de qué credencial y
 * cuándo. Nunca guarda la contraseña en sí, solo el hecho de haberla visto.
 */
class BovedaAcceso extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'boveda_contrasena_id',
        'user_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function credencial(): BelongsTo
    {
        return $this->belongsTo(BovedaContrasena::class, 'boveda_contrasena_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
