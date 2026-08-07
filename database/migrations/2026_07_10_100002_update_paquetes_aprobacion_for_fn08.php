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

        // ── 1. Añadir 'borrador' al ENUM ──────────────────────────────────────
        // SQLite has no ENUM type (TEXT accepts any value), so MODIFY is MySQL-only.
        if ($isMysql) {
            DB::statement("ALTER TABLE paquetes_aprobacion
                MODIFY COLUMN estatus
                ENUM('borrador','pendiente','completado','auto_aprobado')
                NOT NULL DEFAULT 'borrador'");
        }

        Schema::table('paquetes_aprobacion', function (Blueprint $table) {
            // ── 2. Hacer nullable fecha_envio y fecha_limite ──────────────────
            $table->date('fecha_envio')->nullable()->change();
            $table->date('fecha_limite')->nullable()->change();

            // ── 3. Motivo de finalización ─────────────────────────────────────
            if (!Schema::hasColumn('paquetes_aprobacion', 'motivo_finalizacion')) {
                $table->enum('motivo_finalizacion', ['aprobada_por_cliente', 'aprobada_automaticamente'])
                      ->nullable()
                      ->after('estatus');
            }

            // ── 4. Snapshot del período de renovación ─────────────────────────
            if (!Schema::hasColumn('paquetes_aprobacion', 'fecha_inicio_periodo')) {
                $table->date('fecha_inicio_periodo')->nullable()->after('motivo_finalizacion');
            }
            if (!Schema::hasColumn('paquetes_aprobacion', 'fecha_vencimiento_periodo')) {
                $table->date('fecha_vencimiento_periodo')->nullable()->after('fecha_inicio_periodo');
            }
        });

        // ── 5. Gestión del índice plain + eliminación del unique composite ────
        if ($isMysql) {
            // MySQL: usar SHOW INDEX para idempotencia
            $hasPlainIdx = collect(DB::select(
                "SHOW INDEX FROM paquetes_aprobacion WHERE Key_name = 'paquetes_aprobacion_cliente_id_idx'"
            ))->isNotEmpty();

            if (!$hasPlainIdx) {
                Schema::table('paquetes_aprobacion', fn ($t) =>
                    $t->index('cliente_id', 'paquetes_aprobacion_cliente_id_idx')
                );
            }

            $hasCompositeUnique = collect(DB::select(
                "SHOW INDEX FROM paquetes_aprobacion WHERE Key_name = 'paquetes_aprobacion_cliente_id_mes_revision_unique'"
            ))->isNotEmpty();

            if ($hasCompositeUnique) {
                Schema::table('paquetes_aprobacion', fn ($t) =>
                    $t->dropUnique(['cliente_id', 'mes_revision'])
                );
            }

            // ── 6. Columna generada + unique ──────────────────────────────────
            if (!Schema::hasColumn('paquetes_aprobacion', 'cliente_id_activo')) {
                DB::statement("ALTER TABLE paquetes_aprobacion
                    ADD COLUMN cliente_id_activo INT
                    AS (IF(estatus IN ('borrador','pendiente'), cliente_id, NULL)) VIRTUAL");
            }

            $hasUniqueActivo = collect(DB::select(
                "SHOW INDEX FROM paquetes_aprobacion WHERE Key_name = 'uq_un_paquete_activo_por_cliente'"
            ))->isNotEmpty();

            if (!$hasUniqueActivo) {
                DB::statement("ALTER TABLE paquetes_aprobacion
                    ADD UNIQUE INDEX uq_un_paquete_activo_por_cliente (cliente_id_activo)");
            }
        } else {
            // SQLite: add plain index + drop composite unique via Schema (both work natively).
            // Generated columns are MySQL-only; the app-level guard enforces the invariant.
            $existingIndexes = collect(Schema::getIndexes('paquetes_aprobacion'))->pluck('name');

            Schema::table('paquetes_aprobacion', function (Blueprint $table) use ($existingIndexes) {
                if (!$existingIndexes->contains('paquetes_aprobacion_cliente_id_idx')) {
                    $table->index('cliente_id', 'paquetes_aprobacion_cliente_id_idx');
                }
                if ($existingIndexes->contains('paquetes_aprobacion_cliente_id_mes_revision_unique')) {
                    $table->dropUnique(['cliente_id', 'mes_revision']);
                }
            });
        }
    }

    public function down(): void
    {
        $isMysql = DB::getDriverName() === 'mysql';

        if ($isMysql) {
            $hasUniqueActivo = collect(DB::select(
                "SHOW INDEX FROM paquetes_aprobacion WHERE Key_name = 'uq_un_paquete_activo_por_cliente'"
            ))->isNotEmpty();
            if ($hasUniqueActivo) {
                DB::statement("ALTER TABLE paquetes_aprobacion DROP INDEX uq_un_paquete_activo_por_cliente");
            }

            if (Schema::hasColumn('paquetes_aprobacion', 'cliente_id_activo')) {
                DB::statement("ALTER TABLE paquetes_aprobacion DROP COLUMN cliente_id_activo");
            }
        }

        Schema::table('paquetes_aprobacion', function (Blueprint $table) use ($isMysql) {
            $table->dropColumn(array_filter([
                Schema::hasColumn('paquetes_aprobacion', 'motivo_finalizacion')       ? 'motivo_finalizacion' : null,
                Schema::hasColumn('paquetes_aprobacion', 'fecha_inicio_periodo')      ? 'fecha_inicio_periodo' : null,
                Schema::hasColumn('paquetes_aprobacion', 'fecha_vencimiento_periodo') ? 'fecha_vencimiento_periodo' : null,
            ]));

            $table->date('fecha_envio')->nullable(false)->change();
            $table->date('fecha_limite')->nullable(false)->change();

            $existingIndexes = collect(Schema::getIndexes('paquetes_aprobacion'))->pluck('name');

            if (!$existingIndexes->contains('paquetes_aprobacion_cliente_id_mes_revision_unique')) {
                $table->unique(['cliente_id', 'mes_revision']);
            }

            if ($existingIndexes->contains('paquetes_aprobacion_cliente_id_idx')) {
                $table->dropIndex('paquetes_aprobacion_cliente_id_idx');
            }
        });

        if ($isMysql) {
            DB::statement("ALTER TABLE paquetes_aprobacion
                MODIFY COLUMN estatus
                ENUM('pendiente','completado','auto_aprobado')
                NOT NULL DEFAULT 'pendiente'");
        }
    }
};
