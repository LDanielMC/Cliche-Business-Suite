<?php

namespace Tests\Feature;

use App\Mail\NotificacionExpirarReserva;
use App\Models\CalendarioFoto;
use App\Models\FotoAprobacion;
use App\Models\PaqueteAprobacion;
use App\Notifications\FotosVencidasSinAgendarDigest;
use App\Notifications\ReservaPorExpirarDigest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * reservas:expirar — aviso de fotos aprobadas sin agendar hace más de
 * meses_reserva (mismo trato que el Banco de Reserva: solo notifica).
 */
class ExpirarReservasTest extends TestCase
{
    use RefreshDatabase;

    private function crearPaqueteConVencimiento(\Illuminate\Support\Carbon $vencimiento): PaqueteAprobacion
    {
        [$cliente] = $this->crearClienteActivo(3);

        return PaqueteAprobacion::create([
            'cliente_id'                => $cliente->id,
            'mes_revision'              => $vencimiento->format('Y-m'),
            'cantidad_requerida'        => 3,
            'estatus'                   => PaqueteAprobacion::ESTATUS_AUTO_APROBADO,
            'fecha_inicio_periodo'      => $vencimiento->copy()->subDays(29),
            'fecha_vencimiento_periodo' => $vencimiento,
        ]);
    }

    public function test_avisa_de_fotos_aprobadas_sin_agendar_hace_mas_de_meses_reserva(): void
    {
        Mail::fake();
        Notification::fake();
        Storage::fake('public');

        $mesesReserva = config('renovaciones.meses_reserva', 6);

        $admin = $this->crearAdmin();

        $paqueteViejo = $this->crearPaqueteConVencimiento(
            Carbon::today()->subMonths($mesesReserva)->subDay()
        );
        $fotoVieja = $this->crearFoto($paqueteViejo, null, FotoAprobacion::ESTATUS_APROBADA);

        $this->artisan('reservas:expirar')->assertSuccessful();

        // Avisa por campana Y por correo (la propia notificación manda ambos,
        // ya no hay una clase Mail separada y duplicada para esto).
        Notification::assertSentTo($admin, FotosVencidasSinAgendarDigest::class, function ($n, $channels) use ($fotoVieja) {
            return $n->fotos->pluck('id')->contains($fotoVieja->id) && in_array('mail', $channels);
        });
    }

    public function test_avisa_de_fotos_en_reserva_por_expirar(): void
    {
        Mail::fake();
        Notification::fake();
        Storage::fake('public');

        $admin = $this->crearAdmin();
        $operador = $this->crearAdmin(role: \App\Models\User::ROLE_OPERADOR);

        $paquete = $this->crearPaqueteConVencimiento(Carbon::today()->subDays(10));
        $fotoEnReserva = $this->crearFoto($paquete, null, FotoAprobacion::ESTATUS_CONSERVADA);
        $fotoEnReserva->update([
            'cliente_id'               => $paquete->cliente_id,
            'fecha_ingreso_reserva'    => Carbon::today()->subMonths(6),
            'fecha_expiracion_reserva' => Carbon::today()->subDay(),
        ]);

        $this->artisan('reservas:expirar')->assertSuccessful();

        Mail::assertSent(NotificacionExpirarReserva::class);

        Notification::assertSentTo($admin, ReservaPorExpirarDigest::class);
        Notification::assertSentTo($operador, ReservaPorExpirarDigest::class);
    }

    public function test_no_avisa_de_fotos_vencidas_hace_menos_de_meses_reserva(): void
    {
        Notification::fake();
        Storage::fake('public');

        $admin = $this->crearAdmin();
        $paqueteReciente = $this->crearPaqueteConVencimiento(Carbon::today()->subDays(10));
        $this->crearFoto($paqueteReciente, null, FotoAprobacion::ESTATUS_APROBADA);

        $this->artisan('reservas:expirar')->assertSuccessful();

        Notification::assertNotSentTo($admin, FotosVencidasSinAgendarDigest::class);
    }

    public function test_no_avisa_de_fotos_que_ya_se_agendaron(): void
    {
        Notification::fake();
        Storage::fake('public');

        $mesesReserva = config('renovaciones.meses_reserva', 6);
        $admin = $this->crearAdmin();

        $paqueteViejo = $this->crearPaqueteConVencimiento(
            Carbon::today()->subMonths($mesesReserva)->subDay()
        );
        $foto = $this->crearFoto($paqueteViejo, null, FotoAprobacion::ESTATUS_APROBADA);

        CalendarioFoto::create([
            'cliente_id'                   => $paqueteViejo->cliente_id,
            'foto_aprobacion_id'           => $foto->id,
            'fotografia_asociada'          => $foto->ruta_foto,
            'fecha_publicacion_programada' => Carbon::today(),
            'estatus'                      => CalendarioFoto::ESTATUS_PROGRAMADA,
            'creado_por'                   => $admin->id,
        ]);

        $this->artisan('reservas:expirar')->assertSuccessful();

        Notification::assertNotSentTo($admin, FotosVencidasSinAgendarDigest::class);
    }
}
