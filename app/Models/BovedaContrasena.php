<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BovedaContrasena extends Model
{
    protected $table = 'boveda_contrasenas';

    protected $fillable = [
        'nombre_servicio',
        'url',
        'usuario',
        'password',
        'notas',
        'creado_por',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'encrypted',
        ];
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }
}
