<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_renovaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('control_renovacion_id')->nullable()->constrained('control_renovaciones')->nullOnDelete();
            $table->date('periodo_inicio');
            $table->date('periodo_fin');
            $table->date('fecha_pago');
            $table->decimal('monto', 10, 2);
            $table->string('referencia_bancaria', 100)->nullable();
            $table->boolean('solicita_factura')->default(false);
            $table->string('comprobante_pago')->nullable();
            $table->string('factura_pdf')->nullable();
            $table->string('factura_xml')->nullable();
            $table->foreignId('validado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('fecha_validacion')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_renovaciones');
    }
};
