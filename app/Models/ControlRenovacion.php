<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class ControlRenovacion extends Model
{
    // ── Service period statuses ────────────────────────────────────────────────
    const ESTATUS_VIGENTE           = 'vigente';
    const ESTATUS_POR_VENCER        = 'por_vencer';
    const ESTATUS_EN_REVISION       = 'en_revision';
    const ESTATUS_PAGO_RECHAZADO    = 'pago_rechazado';
    const ESTATUS_PAGO_VALIDADO     = 'pago_validado';
    const ESTATUS_FACTURA_PENDIENTE = 'factura_pendiente';
    const ESTATUS_VENCIDO           = 'vencido';

    const ESTATUS = [
        self::ESTATUS_VIGENTE,
        self::ESTATUS_POR_VENCER,
        self::ESTATUS_EN_REVISION,
        self::ESTATUS_PAGO_RECHAZADO,
        self::ESTATUS_PAGO_VALIDADO,
        self::ESTATUS_FACTURA_PENDIENTE,
        self::ESTATUS_VENCIDO,
    ];

    /** Statuses where the client can still submit/resubmit a payment proof */
    const ESTATUS_PUEDE_RENOVAR = [
        self::ESTATUS_POR_VENCER,
        self::ESTATUS_PAGO_RECHAZADO,
        self::ESTATUS_VENCIDO,
    ];

    /** Statuses where there is an active payment process in flight */
    const ESTATUS_EN_PROCESO = [
        self::ESTATUS_EN_REVISION,
        self::ESTATUS_PAGO_VALIDADO,
        self::ESTATUS_FACTURA_PENDIENTE,
    ];

    protected $table = 'control_renovaciones';

    protected $fillable = [
        'cliente_id',
        'fecha_inicio',
        'fecha_vencimiento',
        'estatus',
        'fecha_recordatorio',
        'observaciones',
        'solicitud_renovacion',
        'fecha_solicitud_renovacion',
        // Payment proof
        'comprobante_pago',
        'fecha_pago_cliente',
        'referencia_bancaria',
        'monto',
        'forma_pago',
        // Invoice
        'solicita_factura',
        'motivo_rechazo',
        'rfc',
        'razon_social',
        'codigo_postal_fiscal',
        'regimen_fiscal',
        'uso_cfdi',
        'factura_pdf',
        'factura_xml',
        // Validation
        'validado_por',
        'fecha_validacion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio'               => 'date',
            'fecha_vencimiento'          => 'date',
            'fecha_recordatorio'         => 'date',
            'fecha_pago_cliente'         => 'date',
            'fecha_validacion'           => 'datetime',
            'solicita_factura'           => 'boolean',
            'monto'                      => 'decimal:2',
            'solicitud_renovacion'       => 'boolean',
            'fecha_solicitud_renovacion' => 'datetime',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────────

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function validador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validado_por')->withDefault();
    }

    public function historial(): HasMany
    {
        return $this->hasMany(HistorialRenovacion::class);
    }

    // ── Accessors ──────────────────────────────────────────────────────────────

    public function getComprobanteUrlAttribute(): ?string
    {
        return $this->comprobante_pago
            ? Storage::disk('public')->url($this->comprobante_pago)
            : null;
    }

    public function getFacturaPdfUrlAttribute(): ?string
    {
        return $this->factura_pdf
            ? Storage::disk('public')->url($this->factura_pdf)
            : null;
    }

    public function getFacturaXmlUrlAttribute(): ?string
    {
        return $this->factura_xml
            ? Storage::disk('public')->url($this->factura_xml)
            : null;
    }

    // ── Computed helpers ───────────────────────────────────────────────────────

    public function diasRestantes(): int
    {
        return (int) now()->startOfDay()->diffInDays($this->fecha_vencimiento, false);
    }

    public function puedeRenovar(): bool
    {
        return in_array($this->estatus, self::ESTATUS_PUEDE_RENOVAR);
    }

    public function tieneSolicitudPendiente(): bool
    {
        return (bool) $this->solicitud_renovacion;
    }

    /**
     * True while fecha_vencimiento + dias_gracia has not yet elapsed. The automatic
     * suspension cron (VerificarRenovaciones) only suspends a client once this goes
     * negative, so for an already-suspended client this being true specifically means
     * an admin registered a fresh cycle since the suspension — they can submit a
     * payment proof directly instead of requesting a new cycle.
     */
    public function dentroDePeriodoGracia(): bool
    {
        $diasGracia = config('renovaciones.dias_gracia', 5);

        return now()->startOfDay()->diffInDays(
            $this->fecha_vencimiento->copy()->addDays($diasGracia), false
        ) >= 0;
    }

    public function enProceso(): bool
    {
        return in_array($this->estatus, self::ESTATUS_EN_PROCESO);
    }

    public function facturaCompleta(): bool
    {
        return !empty($this->factura_pdf) && !empty($this->factura_xml);
    }
}
