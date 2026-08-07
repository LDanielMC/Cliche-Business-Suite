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
        Schema::create('pagos_clientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->string('concepto_servicio', 200);
            $table->decimal('monto', 10, 2);
            $table->date('fecha_pago')->nullable();
            $table->string('periodo_facturado', 100)->nullable();
            $table->enum('forma_pago', ['efectivo', 'transferencia', 'tarjeta', 'otro'])->nullable();
            $table->enum('estatus', ['pagado', 'pendiente', 'vencido'])->default('pendiente');
            $table->date('fecha_vencimiento')->nullable();
            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos_clientes');
    }
};
