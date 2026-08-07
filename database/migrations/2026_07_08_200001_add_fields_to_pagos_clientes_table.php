<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos_clientes', function (Blueprint $table) {
            // Link to the archived renovation period that originated this payment
            $table->foreignId('historial_renovacion_id')
                  ->nullable()
                  ->constrained('historial_renovaciones')
                  ->nullOnDelete()
                  ->after('id');

            // Service period covered by this payment
            $table->date('periodo_inicio')->nullable()->after('concepto_servicio');
            $table->date('periodo_fin')->nullable()->after('periodo_inicio');

            // Supporting documents
            $table->string('comprobante_pago')->nullable()->after('forma_pago');
            $table->string('factura_pdf')->nullable()->after('comprobante_pago');
            $table->string('factura_xml')->nullable()->after('factura_pdf');

            // Validation audit trail
            $table->foreignId('validado_por')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete()
                  ->after('factura_xml');
            $table->dateTime('fecha_validacion')->nullable()->after('validado_por');
        });
    }

    public function down(): void
    {
        Schema::table('pagos_clientes', function (Blueprint $table) {
            $table->dropForeign(['historial_renovacion_id']);
            $table->dropForeign(['validado_por']);
            $table->dropColumn([
                'historial_renovacion_id',
                'periodo_inicio',
                'periodo_fin',
                'comprobante_pago',
                'factura_pdf',
                'factura_xml',
                'validado_por',
                'fecha_validacion',
            ]);
        });
    }
};
