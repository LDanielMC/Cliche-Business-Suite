<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cuenta cuántas veces una foto ha sido marcada "conservada" (Banco de
 * Reserva). Sin esto, una foto podía entrar en un ciclo indefinido:
 * reingresarse a un paquete nuevo, el cliente la vuelve a marcar
 * "conservar" en vez de aprobarla o descartarla, y su fecha_ingreso_reserva
 * se reinicia cada vez — nunca llega a expirar ni a forzar una decisión.
 * Al llegar al límite (config('renovaciones.max_veces_conservada')), se
 * marca 'descartada' en vez de 'conservada' para cortar el ciclo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fotos_aprobacion', function (Blueprint $table) {
            $table->unsignedInteger('veces_conservada')->default(0)->after('estatus');
        });
    }

    public function down(): void
    {
        Schema::table('fotos_aprobacion', function (Blueprint $table) {
            $table->dropColumn('veces_conservada');
        });
    }
};
