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
        Schema::create('fotos_aprobacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paquete_aprobacion_id')->constrained('paquetes_aprobacion')->cascadeOnDelete();
            $table->string('ruta_foto');
            $table->enum('estatus', ['pendiente', 'aprobada', 'descartada', 'conservada'])->default('pendiente');
            $table->text('comentarios')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fotos_aprobacion');
    }
};
