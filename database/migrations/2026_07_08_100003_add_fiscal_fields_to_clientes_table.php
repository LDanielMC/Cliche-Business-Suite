<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            if (!Schema::hasColumn('clientes', 'rfc')) {
                $table->string('rfc', 20)->nullable()->after('fecha_registro');
            }
            if (!Schema::hasColumn('clientes', 'razon_social')) {
                $table->string('razon_social')->nullable()->after('rfc');
            }
            if (!Schema::hasColumn('clientes', 'codigo_postal_fiscal')) {
                $table->string('codigo_postal_fiscal', 10)->nullable()->after('razon_social');
            }
            if (!Schema::hasColumn('clientes', 'regimen_fiscal')) {
                $table->string('regimen_fiscal')->nullable()->after('codigo_postal_fiscal');
            }
            if (!Schema::hasColumn('clientes', 'uso_cfdi')) {
                $table->string('uso_cfdi', 10)->nullable()->after('regimen_fiscal');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            foreach (['rfc', 'razon_social', 'codigo_postal_fiscal', 'regimen_fiscal', 'uso_cfdi'] as $col) {
                if (Schema::hasColumn('clientes', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
