<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Vehiculo;
use Faker\Factory as Faker;

class VehiculoSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('es_ES');
        
        $tipos = ['camion', 'furgoneta', 'autobus', 'camioneta'];
        $estados = ['activo', 'mantenimiento', 'inactivo'];
        $marcas = ['Mercedes-Benz', 'Volvo', 'Scania', 'MAN', 'Iveco', 'Renault', 'Ford', 'Chevrolet'];
        
        for ($i = 0; $i < 25; $i++) {
            Vehiculo::create([
                'placa' => strtoupper($faker->bothify('???-####')),
                'marca' => $faker->randomElement($marcas),
                'modelo' => $faker->randomElement(['FH', 'FM', 'Actros', 'R-Series', 'T-GX', 'Master']),
                'anio' => $faker->numberBetween(2015, 2025),
                'vin' => $faker->bothify('XXXXXXXXXXXXXXXXX'),
                'numero_motor' => $faker->bothify('MOTOR-#####'),
                'tipo' => $faker->randomElement($tipos),
                'estado' => $faker->randomElement($estados),
                'fecha_compra' => $faker->dateTimeBetween('-5 years', 'now'),
                'kilometraje_actual' => $faker->numberBetween(0, 250000),
            ]);
        }
    }
}