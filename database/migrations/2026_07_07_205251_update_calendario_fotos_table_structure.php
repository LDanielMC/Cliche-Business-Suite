<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calendario_fotos', function (Blueprint $table) {
            // El migration de creación ya incluye los nombres finales.
            // Solo aplicamos cambios si todavía existen los nombres originales.

            if (Schema::hasColumn('calendario_fotos', 'fecha_publicacion')) {
                $table->renameColumn('fecha_publicacion', 'fecha_publicacion_programada_temp');
                $table->dateTime('fecha_publicacion_programada')->after('cliente_id');
                $table->dropColumn('fecha_publicacion_programada_temp');
            }

            if (Schema::hasColumn('calendario_fotos', 'descripcion')) {
                $table->renameColumn('descripcion', 'fotografia_asociada');
            }

            if (Schema::hasColumn('calendario_fotos', 'estado')) {
                $table->renameColumn('estado', 'estatus');
            }

            if (!Schema::hasColumn('calendario_fotos', 'observaciones')) {
                $table->text('observaciones')->nullable()->after('estatus');
            }
        });
    }

    public function down(): void
    {
        Schema::table('calendario_fotos', function (Blueprint $table) {
            if (Schema::hasColumn('calendario_fotos', 'fecha_publicacion_programada')) {
                $table->renameColumn('fecha_publicacion_programada', 'fecha_publicacion_programada_temp');
                $table->date('fecha_publicacion')->after('cliente_id');
                $table->dropColumn('fecha_publicacion_programada_temp');
            }

            if (Schema::hasColumn('calendario_fotos', 'fotografia_asociada')) {
                $table->renameColumn('fotografia_asociada', 'descripcion');
            }

            if (Schema::hasColumn('calendario_fotos', 'estatus')) {
                $table->renameColumn('estatus', 'estado');
            }

            if (Schema::hasColumn('calendario_fotos', 'observaciones')) {
                $table->dropColumn('observaciones');
            }
        });
    }
};
