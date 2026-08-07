<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PagoCliente extends Model
{
    // All payments generated from this point forward will always be 'pagado'.
    // Legacy records may have 'pendiente' or 'vencido' from the old manual CRUD.
    const ESTATUS_PAGADO   = 'pagado';
    const ESTATUS_PENDIENTE = 'pendiente';
    const ESTATUS_VENCIDO  = 'vencido';

    const ESTATUS    = [self::ESTATUS_PAGADO, self::ESTATUS_PENDIENTE, self::ESTATUS_VENCIDO];
    const FORMA_PAGO = ['efectivo', 'transferencia', 'tarjeta', 'otro'];

    protected $table = 'pagos_clientes';

    protected $fillable = [
        'cliente_id',
        'historial_renovacion_id',
        // Payment details
        'concepto_servicio',
        'monto',
        'fecha_pago',
        'periodo_inicio',
        'periodo_fin',
        'forma_pago',
        'estatus',
        // Supporting documents
        'comprobante_pago',
        'factura_pdf',
        'factura_xml',
        // Validation
        'validado_por',
        'fecha_validacion',
        // Legacy fields (kept for backwards compatibility)
        'periodo_facturado',
        'fecha_vencimiento',
        'registrado_por',
    ];

    protected function casts(): array
    {
        return [
            'fecha_pago'       => 'date',
            'periodo_inicio'   => 'date',
            'periodo_fin'      => 'date',
            'fecha_vencimiento' => 'date',
            'fecha_validacion' => 'datetime',
            'monto'            => 'decimal:2',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────────

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function historialRenovacion(): BelongsTo
    {
        return $this->belongsTo(HistorialRenovacion::class)->withDefault();
    }

    public function validador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validado_por')->withDefault();
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por')->withDefault();
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopePagados($query)
    {
        return $query->where('estatus', self::ESTATUS_PAGADO);
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
}
