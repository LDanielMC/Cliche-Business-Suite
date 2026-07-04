<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->enum('estatus', ['activo', 'inactivo', 'dado_de_baja'])->default('activo')->after('precio_mensual');
            $table->date('fecha_baja')->nullable()->after('estatus');
            $table->dropSoftDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn(['estatus', 'fecha_baja']);
            $table->softDeletes();
        });
    }
};
