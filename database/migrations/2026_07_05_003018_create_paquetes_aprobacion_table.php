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
        Schema::create('paquetes_aprobacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->unsignedTinyInteger('mes');
            $table->unsignedSmallInteger('anio');
            $table->unsignedInteger('cantidad_requerida');
            $table->date('fecha_limite');
            $table->enum('estado', ['pendiente', 'completado', 'auto_aprobado'])->default('pendiente');
            $table->timestamps();

            $table->unique(['cliente_id', 'mes', 'anio']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paquetes_aprobacion');
    }
};
