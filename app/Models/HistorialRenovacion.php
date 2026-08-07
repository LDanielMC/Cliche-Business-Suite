<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class HistorialRenovacion extends Model
{
    protected $table = 'historial_renovaciones';

    protected $fillable = [
        'cliente_id',
        'control_renovacion_id',
        'periodo_inicio',
        'periodo_fin',
        'fecha_pago',
        'monto',
        'referencia_bancaria',
        'solicita_factura',
        'comprobante_pago',
        'factura_pdf',
        'factura_xml',
        'validado_por',
        'fecha_validacion',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'periodo_inicio'   => 'date',
            'periodo_fin'      => 'date',
            'fecha_pago'       => 'date',
            'fecha_validacion' => 'datetime',
            'solicita_factura' => 'boolean',
            'monto'            => 'decimal:2',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────────────────

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function controlRenovacion(): BelongsTo
    {
        return $this->belongsTo(ControlRenovacion::class)->withDefault();
    }

    public function validador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validado_por')->withDefault();
    }

    public function pagoGenerado(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PagoCliente::class, 'historial_renovacion_id');
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
