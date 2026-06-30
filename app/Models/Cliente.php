<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'nombre_negocio',
        'giro',
        'direccion',
        'servicio_contratado',
        'cantidad_fotos',
        'precio_mensual',
        'fecha_registro',
    ];

    protected function casts(): array
    {
        return [
            'fecha_registro' => 'datetime',
            'precio_mensual' => 'decimal:2',
            'deleted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActivos($query)
    {
        return $query->whereNull('deleted_at');
    }

    public function scopeEliminados($query)
    {
        return $query->whereNotNull('deleted_at');
    }
}
