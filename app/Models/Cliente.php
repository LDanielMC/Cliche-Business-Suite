<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nombre_negocio',
        'giro',
        'direccion',
        'servicio_contratado',
        'cantidad_fotos',
        'precio_mensual',
        'fecha_registro',
        // Fiscal data for invoicing
        'rfc',
        'razon_social',
        'codigo_postal_fiscal',
        'regimen_fiscal',
        'uso_cfdi',
    ];

    protected function casts(): array
    {
        return [
            'fecha_registro' => 'datetime',
            'precio_mensual' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function renovaciones(): HasMany
    {
        return $this->hasMany(ControlRenovacion::class);
    }

    public function estatusLogs(): HasMany
    {
        return $this->hasMany(ClienteEstatusLog::class)->orderBy('fecha_evento');
    }

    public function historialRenovaciones(): HasMany
    {
        return $this->hasMany(HistorialRenovacion::class)->orderByDesc('created_at');
    }

    public function renovacionActiva(): HasOne
    {
        return $this->hasOne(ControlRenovacion::class)->latestOfMany();
    }

    public function scopeActivos($query)
    {
        return $query->whereHas('user', fn ($q) => $q->where('estatus', User::ESTATUS_ACTIVO));
    }

    public function scopeInactivos($query)
    {
        return $query->whereHas('user', fn ($q) => $q->where('estatus', User::ESTATUS_INACTIVO));
    }

    public function scopeDadosDeBaja($query)
    {
        return $query->whereHas('user', fn ($q) => $q->where('estatus', User::ESTATUS_DADO_DE_BAJA));
    }

    public function scopeSuspendidos($query)
    {
        return $query->whereHas('user', fn ($q) => $q->where('estatus', User::ESTATUS_SUSPENDIDO));
    }
}
