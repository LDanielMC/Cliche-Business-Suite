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
            $table->string('nombre_servicio', 150);
            $table->string('url')->nullable();
            $table->string('usuario', 150);
            $table->text('password');
            $table->string('notas')->nullable();
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
