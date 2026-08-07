<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE calendario_fotos MODIFY COLUMN estatus ENUM('programada','publicada','cancelada','pausada') DEFAULT 'programada'");
            DB::statement("ALTER TABLE cliente_estatus_logs MODIFY COLUMN evento ENUM('alta','baja','suspension','reactivacion','restauracion')");
        } else {
            // SQLite: the ENUM columns were created with CHECK constraints that only include
            // the original values. Since MODIFY COLUMN is not supported in SQLite, convert
            // both columns to plain varchar, which removes the CHECK and allows all values.
            Schema::table('cliente_estatus_logs', function (Blueprint $table) {
                $table->string('evento')->change();
            });
            Schema::table('calendario_fotos', function (Blueprint $table) {
                $table->string('estatus')->default('programada')->change();
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE calendario_fotos MODIFY COLUMN estatus ENUM('programada','publicada','cancelada') DEFAULT 'programada'");
            DB::statement("ALTER TABLE cliente_estatus_logs MODIFY COLUMN evento ENUM('alta','baja','suspension','reactivacion')");
        }
    }
};
