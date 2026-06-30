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
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('nombre_negocio', 150);
            $table->string('giro', 100)->nullable();
            $table->text('direccion')->nullable();
            $table->string('servicio_contratado', 150)->nullable();
            $table->integer('cantidad_fotos')->default(0);
            $table->decimal('precio_mensual', 10, 2)->default(0);
            $table->timestamp('fecha_registro')->useCurrent();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
