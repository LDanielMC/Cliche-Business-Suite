<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fotos_aprobacion', function (Blueprint $table) {
            // FN.08 §1 — Prioridad para relleno automático por vencimiento
            $table->unsignedInteger('prioridad')->nullable()->after('estatus');

            // FN.08 §3 — Banco de Reserva: vigencia de fotos en estado 'conservada'
            $table->date('fecha_ingreso_reserva')->nullable()->after('prioridad');
            $table->date('fecha_expiracion_reserva')->nullable()->after('fecha_ingreso_reserva');

            // prioridad única por paquete (NULL no participa en unique, así que
            // fotos sin prioridad asignada no violan la restricción)
            $table->unique(['paquete_aprobacion_id', 'prioridad'], 'fotos_prioridad_unica_por_paquete');
        });
    }

    public function down(): void
    {
        Schema::table('fotos_aprobacion', function (Blueprint $table) {
            $table->dropUnique('fotos_prioridad_unica_por_paquete');
            $table->dropColumn(['prioridad', 'fecha_ingreso_reserva', 'fecha_expiracion_reserva']);
        });
    }
};
