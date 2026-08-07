<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos_clientes', function (Blueprint $table) {
            if (!Schema::hasColumn('pagos_clientes', 'estatus')) {
                $table->enum('estatus', ['pagado', 'pendiente', 'vencido'])->default('pendiente')->after('concepto');
            }

            if (!Schema::hasColumn('pagos_clientes', 'fecha_vencimiento')) {
                $table->date('fecha_vencimiento')->after('estatus');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pagos_clientes', function (Blueprint $table) {
            $columns = array_filter(
                ['estatus', 'fecha_vencimiento'],
                fn ($col) => Schema::hasColumn('pagos_clientes', $col)
            );

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
