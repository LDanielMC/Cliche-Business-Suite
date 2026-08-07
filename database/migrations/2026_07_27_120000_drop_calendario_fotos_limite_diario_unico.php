<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Elimina el tope físico de "una foto activa por cliente por día" en BD.
 *
 * El límite diario ahora es dinámico (calculado en CalendarioFotoController
 * a partir de fotos pendientes / días restantes de vigencia) en vez de un
 * tope fijo de 1, así que la columna generada fecha_activa y su índice único
 * ya no reflejan una invariante real y se retiran.
 *
 * uq_foto_activa (una foto aprobada no puede estar activa dos veces) NO se
 * toca — esa invariante sigue vigente.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // uq_un_activa_por_cliente_dia es el único índice que arranca con
        // cliente_id, así que sostiene la FK calendario_fotos_cliente_id_foreign.
        // Hay que darle a esa FK un índice simple propio antes de poder
        // quitar el compuesto, o MySQL rechaza el DROP (error 1553).
        Schema::table('calendario_fotos', function ($table) {
            $table->index('cliente_id', 'calendario_fotos_cliente_id_index');
        });

        Schema::table('calendario_fotos', function ($table) {
            $table->dropUnique('uq_un_activa_por_cliente_dia');
        });

        DB::statement('ALTER TABLE calendario_fotos DROP COLUMN fecha_activa');
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("
            ALTER TABLE calendario_fotos
            ADD COLUMN fecha_activa DATE
                GENERATED ALWAYS AS (
                    IF(estatus IN ('programada', 'publicada'),
                       DATE(fecha_publicacion_programada),
                       NULL)
                ) VIRTUAL
        ");

        Schema::table('calendario_fotos', function ($table) {
            $table->unique(['cliente_id', 'fecha_activa'], 'uq_un_activa_por_cliente_dia');
            $table->dropIndex('calendario_fotos_cliente_id_index');
        });
    }
};
