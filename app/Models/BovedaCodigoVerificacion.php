<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Código de un solo uso mandado por correo para revelar contraseñas de la
 * Bóveda — segundo factor independiente de la sesión/contraseña de cuenta,
 * igual que el flujo de "olvidé mi contraseña" pero para verificación.
 */
class BovedaCodigoVerificacion extends Model
{
    protected $table = 'boveda_codigos_verificacion';

    protected $fillable = ['user_id', 'code', 'expires_at', 'used_at'];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at'    => 'datetime',
        ];
    }

    public function isValid(): bool
    {
        return $this->used_at === null && $this->expires_at->isFuture();
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
