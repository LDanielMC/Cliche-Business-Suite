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
        Schema::create('gastos_operativos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_gasto_id')->constrained('categorias_gastos')->restrictOnDelete();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->string('concepto', 150);
            $table->decimal('monto', 10, 2);
            $table->date('fecha_gasto');
            $table->string('notas')->nullable();
            $table->foreignId('registrado_por')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gastos_operativos');
    }
};
