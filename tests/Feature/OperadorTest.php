<?php

namespace Tests\Feature;

use App\Mail\AccountActivation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Crear operador: el admin no fija estatus ni contraseña — la cuenta nace
 * activa con una contraseña aleatoria y el operador la establece por correo
 * (mismo flujo que Cliente). El teléfono, si se da, debe ser 10 dígitos.
 */
class OperadorTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_crea_operador_sin_definir_password_ni_estatus(): void
    {
        Mail::fake();
        $admin = $this->crearAdmin();

        $response = $this->actingAs($admin)->post(route('operadores.store'), [
            'email' => 'nuevo.operador@example.com',
            'nombres' => 'Carlos',
            'apellido_paterno' => 'Ramírez',
            'telefono' => '5512345678',
        ]);

        $response->assertRedirect(route('operadores.index'));

        $operador = User::where('email', 'nuevo.operador@example.com')->firstOrFail();
        $this->assertEquals(User::ROLE_OPERADOR, $operador->role);
        $this->assertEquals(User::ESTATUS_ACTIVO, $operador->estatus);
        $this->assertNotNull($operador->activation_token);

        Mail::assertSent(AccountActivation::class, function ($mail) use ($operador) {
            return $mail->hasTo($operador->email);
        });
    }

    public function test_telefono_debe_tener_diez_digitos(): void
    {
        $admin = $this->crearAdmin();

        $response = $this->actingAs($admin)->post(route('operadores.store'), [
            'email' => 'operador2@example.com',
            'nombres' => 'Ana',
            'apellido_paterno' => 'López',
            'telefono' => '12345',
        ]);

        $response->assertSessionHasErrors('telefono');
        $this->assertDatabaseMissing('users', ['email' => 'operador2@example.com']);
    }

    public function test_telefono_no_numerico_es_rechazado(): void
    {
        $admin = $this->crearAdmin();

        $response = $this->actingAs($admin)->post(route('operadores.store'), [
            'email' => 'operador3@example.com',
            'nombres' => 'Luis',
            'apellido_paterno' => 'Pérez',
            'telefono' => '55-1234-56a',
        ]);

        $response->assertSessionHasErrors('telefono');
    }

    public function test_formulario_de_creacion_no_pide_password_ni_estatus(): void
    {
        $admin = $this->crearAdmin();

        $response = $this->actingAs($admin)->get(route('operadores.create'));

        $response->assertOk();
        $response->assertDontSee('name="password"', false);
        $response->assertDontSee('name="estatus"', false);
    }

    public function test_admin_si_puede_cambiar_estatus_y_password_al_editar(): void
    {
        $operador = User::factory()->create([
            'role' => User::ROLE_OPERADOR,
            'estatus' => User::ESTATUS_ACTIVO,
            'nombres' => 'Editable',
            'apellido_paterno' => 'Operador',
        ]);
        $admin = $this->crearAdmin();

        $response = $this->actingAs($admin)->put(route('operadores.update', $operador), [
            'email' => $operador->email,
            'nombres' => 'Editable',
            'apellido_paterno' => 'Operador',
            'telefono' => '5599998888',
            'estatus' => User::ESTATUS_INACTIVO,
        ]);

        $response->assertRedirect(route('operadores.index'));
        $this->assertEquals(User::ESTATUS_INACTIVO, $operador->fresh()->estatus);
    }
}
