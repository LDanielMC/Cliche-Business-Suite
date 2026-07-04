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
        Schema::table('users', function (Blueprint $table) {
            $table->string('nombres', 100)->nullable()->after('name');
            $table->string('apellido_paterno', 100)->nullable()->after('nombres');
            $table->string('apellido_materno', 100)->nullable()->after('apellido_paterno');
            $table->string('telefono', 20)->nullable()->after('password');
            $table->enum('estatus', ['activo', 'inactivo', 'suspendido'])->default('activo')->after('telefono');
            $table->date('fecha_baja')->nullable()->after('estatus');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nombres', 'apellido_paterno', 'apellido_materno', 'telefono', 'estatus', 'fecha_baja']);
        });
    }
};
