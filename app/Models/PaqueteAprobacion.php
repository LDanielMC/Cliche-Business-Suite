<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaqueteAprobacion extends Model
{
    const ESTATUS_BORRADOR      = 'borrador';
    const ESTATUS_PENDIENTE     = 'pendiente';
    const ESTATUS_COMPLETADO    = 'completado';
    const ESTATUS_AUTO_APROBADO = 'auto_aprobado';

    /** Estatus que indican un paquete "activo" (sin cerrar).
     *  La BD garantiza unicidad de estos por cliente vía columna generada. */
    const ESTATUS_ACTIVOS = [self::ESTATUS_BORRADOR, self::ESTATUS_PENDIENTE];

    const MOTIVO_APROBADO_CLIENTE    = 'aprobada_por_cliente';
    const MOTIVO_APROBADO_AUTOMATICO = 'aprobada_automaticamente';

    protected $table = 'paquetes_aprobacion';

    protected $fillable = [
        'cliente_id',
        'mes_revision',
        'fecha_envio',
        'fecha_limite',
        'cantidad_requerida',
        'estatus',
        'motivo_finalizacion',
        'fecha_inicio_periodo',
        'fecha_vencimiento_periodo',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_envio'               => 'date',
            'fecha_limite'              => 'date',
            'fecha_inicio_periodo'      => 'date',
            'fecha_vencimiento_periodo' => 'date',
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

    public function getMesRevisionLegibleAttribute(): string
    {
        return \Carbon\Carbon::createFromFormat('Y-m', $this->mes_revision)->translatedFormat('F Y');
    }

    /**
     * Re-alinea el período congelado del paquete con el ciclo de renovación
     * vigente del cliente. Se llama cuando una renovación se completa
     * (RenovacionController::completarRenovacion) para que un paquete que
     * quedó abierto (borrador/pendiente) durante el cambio de ciclo no se
     * quede apuntando a fechas ya vencidas — de lo contrario, una vez
     * aprobadas, sus fotos nunca podrían programarse en el calendario
     * (CalendarioFotoController exige que la fecha caiga dentro de este
     * período). No toca paquetes ya cerrados (completado/auto_aprobado):
     * esos conservan el período en el que realmente se resolvieron.
     */
    public function resincronizarPeriodo(ControlRenovacion $renovacion): void
    {
        if (!in_array($this->estatus, self::ESTATUS_ACTIVOS)) {
            return;
        }

        if ($this->fecha_inicio_periodo->equalTo($renovacion->fecha_inicio)) {
            return;
        }

        $this->update([
            'fecha_inicio_periodo'      => $renovacion->fecha_inicio,
            'fecha_vencimiento_periodo' => $renovacion->fecha_vencimiento,
            'mes_revision'              => $renovacion->fecha_inicio->format('Y-m'),
        ]);
    }
}
