<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Extend the estatus enum with the full payment workflow states
        // SQLite has no ENUM type (TEXT accepts any value), so MODIFY is only needed for MySQL.
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE control_renovaciones MODIFY COLUMN estatus ENUM(
                'vigente',
                'por_vencer',
                'en_revision',
                'pago_rechazado',
                'pago_validado',
                'factura_pendiente',
                'vencido'
            ) NOT NULL DEFAULT 'vigente'");
        }

        Schema::table('control_renovaciones', function (Blueprint $table) {
            // Payment proof
            if (!Schema::hasColumn('control_renovaciones', 'comprobante_pago')) {
                $table->string('comprobante_pago')->nullable()->after('observaciones');
            }
            if (!Schema::hasColumn('control_renovaciones', 'fecha_pago_cliente')) {
                $table->date('fecha_pago_cliente')->nullable()->after('comprobante_pago');
            }
            if (!Schema::hasColumn('control_renovaciones', 'referencia_bancaria')) {
                $table->string('referencia_bancaria', 100)->nullable()->after('fecha_pago_cliente');
            }
            if (!Schema::hasColumn('control_renovaciones', 'monto')) {
                $table->decimal('monto', 10, 2)->nullable()->after('referencia_bancaria');
            }

            // Invoice request
            if (!Schema::hasColumn('control_renovaciones', 'solicita_factura')) {
                $table->boolean('solicita_factura')->nullable()->after('monto');
            }
            if (!Schema::hasColumn('control_renovaciones', 'motivo_rechazo')) {
                $table->text('motivo_rechazo')->nullable()->after('solicita_factura');
            }

            // Fiscal data (captured at time of invoicing request)
            if (!Schema::hasColumn('control_renovaciones', 'rfc')) {
                $table->string('rfc', 20)->nullable()->after('motivo_rechazo');
            }
            if (!Schema::hasColumn('control_renovaciones', 'razon_social')) {
                $table->string('razon_social')->nullable()->after('rfc');
            }
            if (!Schema::hasColumn('control_renovaciones', 'codigo_postal_fiscal')) {
                $table->string('codigo_postal_fiscal', 10)->nullable()->after('razon_social');
            }
            if (!Schema::hasColumn('control_renovaciones', 'regimen_fiscal')) {
                $table->string('regimen_fiscal')->nullable()->after('codigo_postal_fiscal');
            }
            if (!Schema::hasColumn('control_renovaciones', 'uso_cfdi')) {
                $table->string('uso_cfdi', 10)->nullable()->after('regimen_fiscal');
            }

            // Invoice files
            if (!Schema::hasColumn('control_renovaciones', 'factura_pdf')) {
                $table->string('factura_pdf')->nullable()->after('uso_cfdi');
            }
            if (!Schema::hasColumn('control_renovaciones', 'factura_xml')) {
                $table->string('factura_xml')->nullable()->after('factura_pdf');
            }

            // Admin validation
            if (!Schema::hasColumn('control_renovaciones', 'validado_por')) {
                $table->foreignId('validado_por')->nullable()->constrained('users')->nullOnDelete()->after('factura_xml');
            }
            if (!Schema::hasColumn('control_renovaciones', 'fecha_validacion')) {
                $table->dateTime('fecha_validacion')->nullable()->after('validado_por');
            }
        });
    }

    public function down(): void
    {
        Schema::table('control_renovaciones', function (Blueprint $table) {
            $cols = [
                'validado_por', 'fecha_validacion',
                'factura_pdf', 'factura_xml',
                'rfc', 'razon_social', 'codigo_postal_fiscal', 'regimen_fiscal', 'uso_cfdi',
                'solicita_factura', 'motivo_rechazo',
                'comprobante_pago', 'fecha_pago_cliente', 'referencia_bancaria', 'monto',
            ];

            foreach ($cols as $col) {
                if (Schema::hasColumn('control_renovaciones', $col)) {
                    if ($col === 'validado_por') {
                        $table->dropConstrainedForeignId($col);
                    } else {
                        $table->dropColumn($col);
                    }
                }
            }
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE control_renovaciones MODIFY COLUMN estatus ENUM('vigente', 'por_vencer', 'vencido') NOT NULL DEFAULT 'vigente'");
        }
    }
};
