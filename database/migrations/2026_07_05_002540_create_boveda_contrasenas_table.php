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
        Schema::create('boveda_contrasenas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->string('nombre_plataforma', 100);
            $table->string('url_acceso')->nullable();
            $table->string('usuario', 150);
            // El diccionario define VARCHAR(255), pero el cifrado de Laravel
            // produce cadenas más largas (~350-500 car.); se usa TEXT para no truncar el valor cifrado.
            $table->text('password');
            $table->string('correo_asociado', 150)->nullable();
            $table->text('observaciones')->nullable();
            $table->foreignId('creado_por')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boveda_contrasenas');
    }
};
