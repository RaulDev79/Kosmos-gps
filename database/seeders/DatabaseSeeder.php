<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            VehiculoSeeder::class,
            ConductorSeeder::class,
            ViajeSeeder::class,
            MantenimientoSeeder::class,
            RegistroCombustibleSeeder::class,
        ]);
    }
}