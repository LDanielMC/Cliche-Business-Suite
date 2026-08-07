<?php

namespace Tests\Feature;

use App\Models\CalendarioFoto;
use App\Models\PaqueteAprobacion;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Verifica que el DatabaseSeeder no viole los invariantes del esquema.
 *
 * Propósito: si un seeder queda inconsistente con el schema migrado, este test
 * falla en la suite CI antes de que el error aparezca en el navegador.
 * Los tests de feature usan factories; este test usa el seeder real de desarrollo.
 */
class SeederConsistencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_no_viola_invariantes_del_esquema(): void
    {
        // El seeder usa Storage::disk('public')->put() para generar imágenes demo.
        // Storage::fake intercepta esas escrituras sin tocar el disco real.
        Storage::fake('public');

        $this->seed(DatabaseSeeder::class);

        // ── Invariante 1: ningún CalendarioFoto activo tiene foto_aprobacion_id NULL ──
        $nullActivos = CalendarioFoto::whereIn('estatus', [
            CalendarioFoto::ESTATUS_PROGRAMADA,
            CalendarioFoto::ESTATUS_PUBLICADA,
        ])->whereNull('foto_aprobacion_id')->count();

        $this->assertEquals(0, $nullActivos,
            'Ningún CalendarioFoto activo (programada/publicada) debe tener foto_aprobacion_id NULL.'
        );

        // ── Invariante 2: una foto_aprobacion_id no puede estar activa más de una vez ──
        $dupesFoto = DB::select("
            SELECT foto_aprobacion_id, COUNT(*) AS n
            FROM calendario_fotos
            WHERE estatus IN ('programada','publicada')
              AND foto_aprobacion_id IS NOT NULL
            GROUP BY foto_aprobacion_id
            HAVING n > 1
        ");

        $this->assertCount(0, $dupesFoto,
            'Ningún foto_aprobacion_id puede estar activo en más de un CalendarioFoto.'
        );

        // ── Invariante 3: un cliente no puede tener dos fotos activas el mismo día ──
        $dupesClienteDia = DB::select("
            SELECT cliente_id, DATE(fecha_publicacion_programada) AS dia, COUNT(*) AS n
            FROM calendario_fotos
            WHERE estatus IN ('programada','publicada')
            GROUP BY cliente_id, DATE(fecha_publicacion_programada)
            HAVING n > 1
        ");

        $this->assertCount(0, $dupesClienteDia,
            'Un cliente no puede tener dos fotos activas el mismo día.'
        );

        // ── Invariante 4: paquetes cerrados tienen el snapshot de período ──────────
        $paquetesSinPeriodo = PaqueteAprobacion::whereIn('estatus', [
            PaqueteAprobacion::ESTATUS_COMPLETADO,
            PaqueteAprobacion::ESTATUS_AUTO_APROBADO,
        ])->whereNull('fecha_inicio_periodo')->count();

        $this->assertEquals(0, $paquetesSinPeriodo,
            'Los paquetes completados/auto_aprobados deben tener fecha_inicio_periodo.'
        );

        // ── Invariante 5: no hay más de un paquete activo (borrador/pendiente) por cliente ──
        $dupesPaquete = DB::select("
            SELECT cliente_id, COUNT(*) AS n
            FROM paquetes_aprobacion
            WHERE estatus IN ('borrador','pendiente')
            GROUP BY cliente_id
            HAVING n > 1
        ");

        $this->assertCount(0, $dupesPaquete,
            'Un cliente no puede tener más de un paquete activo (borrador/pendiente) al mismo tiempo.'
        );
    }
}
