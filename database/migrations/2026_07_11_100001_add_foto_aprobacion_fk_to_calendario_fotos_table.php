<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $isMysql = DB::getDriverName() === 'mysql';

        // ── 1. Añadir columna FK (nullable para respetar filas legacy) ──────
        Schema::table('calendario_fotos', function (Blueprint $table) {
            $table->unsignedBigInteger('foto_aprobacion_id')
                  ->nullable()
                  ->after('cliente_id');
        });

        // ── 2. Poblar el FK desde filas legacy (match por ruta, one-time) ──
        if ($isMysql) {
            DB::statement("
                UPDATE calendario_fotos cf
                INNER JOIN fotos_aprobacion fa ON fa.ruta_foto = cf.fotografia_asociada
                SET cf.foto_aprobacion_id = fa.id
                WHERE cf.foto_aprobacion_id IS NULL
            ");
        } else {
            // SQLite uses correlated subquery syntax for UPDATE (no JOIN in UPDATE)
            DB::statement("
                UPDATE calendario_fotos
                SET foto_aprobacion_id = (
                    SELECT id FROM fotos_aprobacion
                    WHERE ruta_foto = calendario_fotos.fotografia_asociada
                    LIMIT 1
                )
                WHERE foto_aprobacion_id IS NULL
            ");
        }

        // ── 3. FK constraint ────────────────────────────────────────────────
        Schema::table('calendario_fotos', function (Blueprint $table) {
            $table->foreign('foto_aprobacion_id', 'cf_foto_aprobacion_fk')
                  ->references('id')
                  ->on('fotos_aprobacion')
                  ->nullOnDelete();
        });

        // ── 4–6. Columnas generadas + unique indexes (MySQL only) ───────────
        // SQLite doesn't support MySQL-style VIRTUAL generated columns.
        // The uniqueness invariants are enforced at application level in those tests.
        if ($isMysql) {
            DB::statement("
                ALTER TABLE calendario_fotos
                ADD COLUMN fecha_activa DATE
                    GENERATED ALWAYS AS (
                        IF(estatus IN ('programada', 'publicada'),
                           DATE(fecha_publicacion_programada),
                           NULL)
                    ) VIRTUAL
            ");

            DB::statement("
                ALTER TABLE calendario_fotos
                ADD COLUMN foto_activa_id BIGINT UNSIGNED
                    GENERATED ALWAYS AS (
                        IF(estatus IN ('programada', 'publicada'),
                           foto_aprobacion_id,
                           NULL)
                    ) VIRTUAL
            ");

            Schema::table('calendario_fotos', function (Blueprint $table) {
                $table->unique(['cliente_id', 'fecha_activa'], 'uq_un_activa_por_cliente_dia');
                $table->unique('foto_activa_id', 'uq_foto_activa');
            });
        }
    }

    public function down(): void
    {
        $isMysql = DB::getDriverName() === 'mysql';

        if ($isMysql) {
            Schema::table('calendario_fotos', function (Blueprint $table) {
                $table->dropUnique('uq_foto_activa');
                $table->dropUnique('uq_un_activa_por_cliente_dia');
            });

            DB::statement('ALTER TABLE calendario_fotos DROP COLUMN foto_activa_id');
            DB::statement('ALTER TABLE calendario_fotos DROP COLUMN fecha_activa');
        }

        Schema::table('calendario_fotos', function (Blueprint $table) {
            $table->dropForeign('cf_foto_aprobacion_fk');
            $table->dropColumn('foto_aprobacion_id');
        });
    }
};
