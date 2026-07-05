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
            $table->string('mes_revision', 50);
            $table->date('fecha_envio');
            $table->date('fecha_limite');
            $table->unsignedInteger('cantidad_requerida');
            $table->enum('estatus', ['pendiente', 'completado', 'auto_aprobado'])->default('pendiente');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->unique(['cliente_id', 'mes_revision']);
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
