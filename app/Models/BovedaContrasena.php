<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BovedaContrasena extends Model
{
    protected $table = 'boveda_contrasenas';

    protected $fillable = [
        'cliente_id',
        'nombre_plataforma',
        'url_acceso',
        'usuario',
        'password',
        'correo_asociado',
        'observaciones',
        'creado_por',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'encrypted',
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
}
