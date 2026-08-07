<?php

namespace Tests\Feature;

use App\Mail\CodigoVerificacionBoveda;
use App\Models\BovedaAcceso;
use App\Models\BovedaCodigoVerificacion;
use App\Models\BovedaContrasena;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BovedaContrasenaTest extends TestCase
{
    use RefreshDatabase;

    private function crearCredencial(string $password = 'SuperSecreta123'): BovedaContrasena
    {
        [$cliente] = $this->crearClienteActivo();
        $admin = $this->crearAdmin();

        return BovedaContrasena::create([
            'cliente_id'         => $cliente->id,
            'nombre_plataforma'  => 'Instagram',
            'usuario'            => 'cliente.instagram',
            'password'           => $password,
            'creado_por'         => $admin->id,
        ]);
    }

    /** Envía y confirma un código válido para el usuario dado, dejando la sesión verificada. */
    private function verificarSesion(User $usuario): void
    {
        Mail::fake();
        $this->actingAs($usuario)->postJson(route('boveda.verificar.enviar-codigo'));
        $codigo = BovedaCodigoVerificacion::where('user_id', $usuario->id)->latest('id')->first();

        $this->actingAs($usuario)->postJson(route('boveda.verificar.confirmar-codigo'), [
            'codigo' => $codigo->code,
        ])->assertOk();
    }

    public function test_la_pagina_de_editar_nunca_expone_la_contrasena_en_el_html(): void
    {
        $admin = $this->crearAdmin();
        $credencial = $this->crearCredencial('NoDebeAparecerEnElHTML');

        $response = $this->actingAs($admin)->get(route('boveda.edit', $credencial));

        $response->assertOk();
        $response->assertDontSee('NoDebeAparecerEnElHTML');
    }

    public function test_revelar_sin_verificar_pide_verificacion(): void
    {
        $admin = $this->crearAdmin();
        $credencial = $this->crearCredencial();

        $response = $this->actingAs($admin)->postJson(route('boveda.revelar', $credencial));

        $response->assertStatus(428);
        $response->assertJson(['requiere_verificacion' => true]);
    }

    public function test_enviar_codigo_manda_un_correo_con_codigo_de_6_digitos(): void
    {
        Mail::fake();
        $admin = $this->crearAdmin();

        $this->actingAs($admin)->postJson(route('boveda.verificar.enviar-codigo'))->assertOk();

        $this->assertDatabaseCount('boveda_codigos_verificacion', 1);
        $codigo = BovedaCodigoVerificacion::first();
        $this->assertEquals($admin->id, $codigo->user_id);
        $this->assertMatchesRegularExpression('/^\d{6}$/', $codigo->code);

        Mail::assertSent(CodigoVerificacionBoveda::class, function ($mail) use ($admin, $codigo) {
            return $mail->hasTo($admin->email) && $mail->code === $codigo->code;
        });
    }

    public function test_confirmar_codigo_incorrecto_es_rechazado(): void
    {
        Mail::fake();
        $admin = $this->crearAdmin();
        $this->actingAs($admin)->postJson(route('boveda.verificar.enviar-codigo'));

        $response = $this->actingAs($admin)->postJson(route('boveda.verificar.confirmar-codigo'), [
            'codigo' => '000000',
        ]);

        $response->assertStatus(422);
    }

    public function test_confirmar_codigo_correcto_permite_revelar(): void
    {
        $admin = $this->crearAdmin();
        $credencial = $this->crearCredencial('MiPasswordReal');

        $this->verificarSesion($admin);

        $response = $this->actingAs($admin)->postJson(route('boveda.revelar', $credencial));

        $response->assertOk();
        $response->assertJson(['password' => 'MiPasswordReal']);
    }

    public function test_un_codigo_ya_usado_no_se_puede_reutilizar(): void
    {
        Mail::fake();
        $admin = $this->crearAdmin();
        $this->actingAs($admin)->postJson(route('boveda.verificar.enviar-codigo'));
        $codigo = BovedaCodigoVerificacion::where('user_id', $admin->id)->latest('id')->first();

        $this->actingAs($admin)->postJson(route('boveda.verificar.confirmar-codigo'), ['codigo' => $codigo->code])
            ->assertOk();

        // Reutilizar el mismo código (ej. otra pestaña) debe fallar.
        $response = $this->actingAs($admin)->postJson(route('boveda.verificar.confirmar-codigo'), ['codigo' => $codigo->code]);

        $response->assertStatus(422);
    }

    public function test_pedir_un_nuevo_codigo_invalida_el_anterior(): void
    {
        Mail::fake();
        $admin = $this->crearAdmin();
        $this->actingAs($admin)->postJson(route('boveda.verificar.enviar-codigo'));
        $primerCodigo = BovedaCodigoVerificacion::where('user_id', $admin->id)->latest('id')->first();

        $this->actingAs($admin)->postJson(route('boveda.verificar.enviar-codigo'));

        $response = $this->actingAs($admin)->postJson(route('boveda.verificar.confirmar-codigo'), [
            'codigo' => $primerCodigo->code,
        ]);

        $response->assertStatus(422);
    }

    public function test_la_verificacion_se_recuerda_por_un_rato_sin_volver_a_pedir_codigo(): void
    {
        $admin = $this->crearAdmin();
        $credencialA = $this->crearCredencial('PrimeraClave');
        [$clienteB] = $this->crearClienteActivo();
        $credencialB = BovedaContrasena::create([
            'cliente_id' => $clienteB->id, 'nombre_plataforma' => 'Facebook',
            'usuario' => 'otro.usuario', 'password' => 'SegundaClave', 'creado_por' => $admin->id,
        ]);

        $this->verificarSesion($admin);
        $this->actingAs($admin)->postJson(route('boveda.revelar', $credencialA))->assertOk();

        // Segunda revelación (otra credencial, misma sesión): ya no debe pedir código de nuevo.
        $response = $this->actingAs($admin)->postJson(route('boveda.revelar', $credencialB));

        $response->assertOk();
        $response->assertJson(['password' => 'SegundaClave']);
    }

    public function test_revelar_responde_sin_cache(): void
    {
        $admin = $this->crearAdmin();
        $credencial = $this->crearCredencial();
        $this->verificarSesion($admin);

        $response = $this->actingAs($admin)->postJson(route('boveda.revelar', $credencial));

        // Symfony reordena las directivas alfabéticamente al normalizar el header.
        $cacheControl = $response->headers->get('Cache-Control');
        $this->assertStringContainsString('no-store', $cacheControl);
        $this->assertStringContainsString('no-cache', $cacheControl);
        $this->assertStringContainsString('private', $cacheControl);
    }

    public function test_revelar_deja_registro_de_auditoria(): void
    {
        $admin = $this->crearAdmin();
        $credencial = $this->crearCredencial();
        $this->verificarSesion($admin);

        $this->assertDatabaseCount('boveda_accesos', 0);

        $this->actingAs($admin)->postJson(route('boveda.revelar', $credencial));

        $this->assertDatabaseHas('boveda_accesos', [
            'boveda_contrasena_id' => $credencial->id,
            'user_id'              => $admin->id,
        ]);
        $this->assertDatabaseCount('boveda_accesos', 1);
    }

    public function test_el_registro_de_auditoria_nunca_guarda_la_contrasena(): void
    {
        $admin = $this->crearAdmin();
        $credencial = $this->crearCredencial('SecretoQueNoDebeQuedarEnLaBitacora');
        $this->verificarSesion($admin);

        $this->actingAs($admin)->postJson(route('boveda.revelar', $credencial));

        $acceso = BovedaAcceso::first();
        $this->assertNotNull($acceso);
        $this->assertArrayNotHasKey('password', $acceso->getAttributes());
    }

    public function test_la_pagina_de_editar_no_muestra_el_historial_de_accesos(): void
    {
        // El registro se sigue guardando (auditoría interna), pero no se
        // expone en la interfaz: solo el admin puede ver contraseñas, así
        // que la lista de "quién lo vio" siempre diría lo mismo.
        $admin = $this->crearAdmin();
        $credencial = $this->crearCredencial();
        $this->verificarSesion($admin);

        $this->actingAs($admin)->postJson(route('boveda.revelar', $credencial));

        $response = $this->actingAs($admin)->get(route('boveda.edit', $credencial));

        $response->assertOk();
        $response->assertDontSee('Últimos accesos');
    }

    public function test_cliente_no_puede_revelar_contrasenas(): void
    {
        [$cliente] = $this->crearClienteActivo();
        $credencial = $this->crearCredencial();

        $this->actingAs($cliente->user)->post(route('boveda.revelar', $credencial))->assertForbidden();
    }

    public function test_operador_no_puede_revelar_contrasenas(): void
    {
        $operador = $this->crearAdmin(role: User::ROLE_OPERADOR);
        $credencial = $this->crearCredencial();

        $this->actingAs($operador)->post(route('boveda.revelar', $credencial))->assertForbidden();
    }

    public function test_formulario_de_crear_incluye_clientes_sin_importar_su_estatus(): void
    {
        $admin = $this->crearAdmin();
        [$clienteSuspendido] = $this->crearClienteActivo();
        $clienteSuspendido->user->update(['estatus' => User::ESTATUS_SUSPENDIDO]);
        [$clienteBaja] = $this->crearClienteActivo();
        $clienteBaja->user->update(['estatus' => User::ESTATUS_DADO_DE_BAJA]);

        $response = $this->actingAs($admin)->get(route('boveda.create'));

        $response->assertOk();
        $response->assertSee($clienteSuspendido->nombre_negocio);
        $response->assertSee($clienteBaja->nombre_negocio);
    }

    public function test_formulario_de_editar_incluye_al_cliente_de_la_credencial_aunque_ya_no_este_activo(): void
    {
        $admin = $this->crearAdmin();
        $credencial = $this->crearCredencial();
        $credencial->cliente->user->update(['estatus' => User::ESTATUS_SUSPENDIDO]);

        $response = $this->actingAs($admin)->get(route('boveda.edit', $credencial));

        $response->assertOk();
        $response->assertSee($credencial->cliente->nombre_negocio);
        // Debe seguir seleccionado, no perderse del <select>.
        $response->assertSee('value="' . $credencial->cliente_id . '" selected', false);
    }

    public function test_crear_credencial_con_confirmacion_de_password_distinta_falla(): void
    {
        $admin = $this->crearAdmin();
        [$cliente] = $this->crearClienteActivo();

        $response = $this->actingAs($admin)->post(route('boveda.store'), [
            'cliente_id' => $cliente->id,
            'nombre_plataforma' => 'Instagram',
            'usuario' => 'usuario.ig',
            'password' => 'ClaveSegura123',
            'password_confirmation' => 'OtraClaveDistinta',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertDatabaseCount('boveda_contrasenas', 0);
    }

    public function test_crear_credencial_con_confirmacion_de_password_correcta_funciona(): void
    {
        $admin = $this->crearAdmin();
        [$cliente] = $this->crearClienteActivo();

        $response = $this->actingAs($admin)->post(route('boveda.store'), [
            'cliente_id' => $cliente->id,
            'nombre_plataforma' => 'Instagram',
            'usuario' => 'usuario.ig',
            'password' => 'ClaveSegura123',
            'password_confirmation' => 'ClaveSegura123',
        ]);

        $response->assertRedirect(route('boveda.index'));
        $this->assertDatabaseHas('boveda_contrasenas', ['usuario' => 'usuario.ig']);
    }

    public function test_cambiar_password_sin_verificar_es_rechazado(): void
    {
        $admin = $this->crearAdmin();
        $credencial = $this->crearCredencial('ClaveOriginal');

        $response = $this->actingAs($admin)->put(route('boveda.update', $credencial), [
            'cliente_id' => $credencial->cliente_id,
            'nombre_plataforma' => $credencial->nombre_plataforma,
            'usuario' => $credencial->usuario,
            'password' => 'ClaveNuevaSinVerificar',
            'password_confirmation' => 'ClaveNuevaSinVerificar',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertEquals('ClaveOriginal', $credencial->fresh()->password);
    }

    public function test_cambiar_password_verificado_si_se_guarda(): void
    {
        $admin = $this->crearAdmin();
        $credencial = $this->crearCredencial('ClaveOriginal');
        $this->verificarSesion($admin);

        $response = $this->actingAs($admin)->put(route('boveda.update', $credencial), [
            'cliente_id' => $credencial->cliente_id,
            'nombre_plataforma' => $credencial->nombre_plataforma,
            'usuario' => $credencial->usuario,
            'password' => 'ClaveNuevaVerificada',
            'password_confirmation' => 'ClaveNuevaVerificada',
        ]);

        $response->assertRedirect(route('boveda.index'));
        $this->assertEquals('ClaveNuevaVerificada', $credencial->fresh()->password);
    }

    public function test_editar_otros_campos_sin_cambiar_password_no_requiere_verificacion(): void
    {
        $admin = $this->crearAdmin();
        $credencial = $this->crearCredencial('ClaveOriginal');

        $response = $this->actingAs($admin)->put(route('boveda.update', $credencial), [
            'cliente_id' => $credencial->cliente_id,
            'nombre_plataforma' => 'Nueva Plataforma',
            'usuario' => $credencial->usuario,
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertRedirect(route('boveda.index'));
        $this->assertEquals('Nueva Plataforma', $credencial->fresh()->nombre_plataforma);
        $this->assertEquals('ClaveOriginal', $credencial->fresh()->password);
    }
}
