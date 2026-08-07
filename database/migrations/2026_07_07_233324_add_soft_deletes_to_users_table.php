<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tablas = ['users', 'clientes', 'gastos_operativos', 'pagos_clientes', 'categorias_gastos'];

        foreach ($tablas as $tabla) {
            if (!Schema::hasColumn($tabla, 'deleted_at')) {
                Schema::table($tabla, function (Blueprint $table) {
                    $table->softDeletes();
                });
            }
        }
    }

    public function down(): void
    {
        $tablas = ['categorias_gastos', 'pagos_clientes', 'gastos_operativos', 'clientes', 'users'];

        foreach ($tablas as $tabla) {
            if (Schema::hasColumn($tabla, 'deleted_at')) {
                Schema::table($tabla, function (Blueprint $table) {
                    $table->dropSoftDeletes();
                });
            }
        }
    }
};
