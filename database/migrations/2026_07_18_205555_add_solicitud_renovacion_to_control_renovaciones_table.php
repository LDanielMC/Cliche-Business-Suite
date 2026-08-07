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
        Schema::table('control_renovaciones', function (Blueprint $table) {
            $table->boolean('solicitud_renovacion')->default(false)->after('observaciones');
            $table->timestamp('fecha_solicitud_renovacion')->nullable()->after('solicitud_renovacion');
        });
    }

    public function down(): void
    {
        Schema::table('control_renovaciones', function (Blueprint $table) {
            $table->dropColumn(['solicitud_renovacion', 'fecha_solicitud_renovacion']);
        });
    }
};
