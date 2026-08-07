<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Las fotos en el Banco de Reserva (estatus 'conservada') ya no dependen de
 * seguir apuntando a un paquete_aprobacion vivo para sobrevivir.
 *
 * Antes, reingresar una foto de reserva a un paquete nuevo reasignaba su
 * paquete_aprobacion_id (FK con cascadeOnDelete). Si ese paquete se borraba
 * (borrador descartado), la cascada se llevaba la foto con él — aunque su
 * estatus ya fuera 'conservada' otra vez. Con cliente_id como referencia
 * propia, una foto de reserva puede quedar con paquete_aprobacion_id NULL
 * sin perder de quién es, y el controlador la desprende del paquete antes
 * de borrarlo en vez de dejar que la cascada decida.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fotos_aprobacion', function (Blueprint $table) {
            $table->foreignId('cliente_id')->nullable()->after('paquete_aprobacion_id')
                ->constrained('clientes')->nullOnDelete();
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('
                UPDATE fotos_aprobacion fa
                JOIN paquetes_aprobacion pa ON pa.id = fa.paquete_aprobacion_id
                SET fa.cliente_id = pa.cliente_id
                WHERE fa.cliente_id IS NULL
            ');
        } else {
            // SQLite (tests) no soporta UPDATE...JOIN — subconsulta correlacionada.
            DB::statement('
                UPDATE fotos_aprobacion
                SET cliente_id = (
                    SELECT cliente_id FROM paquetes_aprobacion
                    WHERE paquetes_aprobacion.id = fotos_aprobacion.paquete_aprobacion_id
                )
                WHERE cliente_id IS NULL
            ');
        }

        Schema::table('fotos_aprobacion', function (Blueprint $table) {
            $table->unsignedBigInteger('paquete_aprobacion_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('fotos_aprobacion', function (Blueprint $table) {
            $table->unsignedBigInteger('paquete_aprobacion_id')->nullable(false)->change();
            $table->dropConstrainedForeignId('cliente_id');
        });
    }
};
