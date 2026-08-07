<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            ClienteSeeder::class,
            CategoriaGastoSeeder::class,
            GastoOperativoSeeder::class,
            PagoClienteSeeder::class,
            ControlRenovacionSeeder::class,   // debe correr antes que PaqueteAprobacionSeeder
            PaqueteAprobacionSeeder::class,   // debe correr antes que CalendarioFotoSeeder
            CalendarioFotoSeeder::class,      // depende de FotoAprobacion ya existentes
            BovedaContrasenaSeeder::class,
        ]);
    }
}
