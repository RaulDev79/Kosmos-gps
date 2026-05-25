<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Conductor;
use Faker\Factory as Faker;

class ConductorSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('es_ES');
        
        $estados = ['activo', 'suspendido', 'inactivo'];
        
        for ($i = 0; $i < 25; $i++) {
            Conductor::create([
                'nombre' => $faker->name(),
                'numero_licencia' => strtoupper($faker->bothify('LIC-#####')),
                'vencimiento_licencia' => $faker->dateTimeBetween('now', '+3 years'),
                'telefono' => $faker->phoneNumber(),
                'fecha_contratacion' => $faker->dateTimeBetween('-8 years', 'now'),
                'estado' => $faker->randomElement($estados),
            ]);
        }
    }
}