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
        Schema::table('calendario_fotos', function (Blueprint $table) {
            $table->foreign('foto_aprobacion_id')->references('id')->on('fotos_aprobacion')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calendario_fotos', function (Blueprint $table) {
            $table->dropForeign(['foto_aprobacion_id']);
        });
    }
};
