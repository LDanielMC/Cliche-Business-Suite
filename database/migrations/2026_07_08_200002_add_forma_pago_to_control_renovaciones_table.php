<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('control_renovaciones', function (Blueprint $table) {
            $table->enum('forma_pago', ['efectivo', 'transferencia', 'tarjeta', 'otro'])
                  ->nullable()
                  ->after('monto');
        });
    }

    public function down(): void
    {
        Schema::table('control_renovaciones', function (Blueprint $table) {
            $table->dropColumn('forma_pago');
        });
    }
};
