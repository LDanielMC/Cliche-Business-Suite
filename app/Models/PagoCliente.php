<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PagoCliente extends Model
{
    const ESTATUS_PAGADO = 'pagado';
    const ESTATUS_PENDIENTE = 'pendiente';
    const ESTATUS_VENCIDO = 'vencido';

    const ESTATUS = [self::ESTATUS_PAGADO, self::ESTATUS_PENDIENTE, self::ESTATUS_VENCIDO];
    const FORMA_PAGO = ['efectivo', 'transferencia', 'tarjeta', 'otro'];

    protected $table = 'pagos_clientes';

    protected $fillable = [
        'cliente_id',
        'concepto_servicio',
        'monto',
        'fecha_pago',
        'periodo_facturado',
        'forma_pago',
        'estatus',
        'fecha_vencimiento',
        'registrado_por',
    ];

    protected function casts(): array
    {
        return [
            'fecha_pago' => 'date',
            'fecha_vencimiento' => 'date',
            'monto' => 'decimal:2',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    public function scopePagados($query)
    {
        return $query->where('estatus', self::ESTATUS_PAGADO);
    }
}
