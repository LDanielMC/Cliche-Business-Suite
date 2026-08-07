<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cliente_estatus_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('evento', ['alta', 'baja', 'reactivacion']);
            $table->date('fecha_evento');
            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });

        // Seed historical data from existing records
        $clientes = DB::table('clientes')
            ->join('users', 'clientes.user_id', '=', 'users.id')
            ->select(
                'clientes.id as cliente_id',
                'clientes.user_id',
                'clientes.fecha_registro',
                'users.estatus',
                'users.fecha_baja'
            )
            ->get();

        $now = now();

        foreach ($clientes as $row) {
            // Register the initial alta event
            DB::table('cliente_estatus_logs')->insert([
                'cliente_id'      => $row->cliente_id,
                'user_id'         => $row->user_id,
                'evento'          => 'alta',
                'fecha_evento'    => $row->fecha_registro
                    ? \Carbon\Carbon::parse($row->fecha_registro)->toDateString()
                    : $now->toDateString(),
                'registrado_por'  => null,
                'observaciones'   => 'Registro migrado automáticamente',
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);

            // If the client is currently deactivated and has a fecha_baja, register that baja
            if ($row->estatus === 'dado_de_baja' && $row->fecha_baja) {
                DB::table('cliente_estatus_logs')->insert([
                    'cliente_id'     => $row->cliente_id,
                    'user_id'        => $row->user_id,
                    'evento'         => 'baja',
                    'fecha_evento'   => \Carbon\Carbon::parse($row->fecha_baja)->toDateString(),
                    'registrado_por' => null,
                    'observaciones'  => 'Registro migrado automáticamente',
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cliente_estatus_logs');
    }
};
