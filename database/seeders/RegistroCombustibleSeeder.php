<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\RegistroCombustible;
use App\Models\Vehiculo;
use App\Models\Conductor;
use Faker\Factory as Faker;


class RegistroCombustibleSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('es_ES');
        
        $vehiculos = Vehiculo::pluck('id')->toArray();
        $conductores = Conductor::pluck('id')->toArray();
        $estaciones = ['Terpel', 'Primax', 'Petrobras', 'Shell', 'Texaco', 'Servicentro'];
        
        for ($i = 0; $i < 25; $i++) {
            $litros = $faker->randomFloat(2, 20, 300);
            $precioLitro = $faker->randomFloat(2, 0.8, 1.5);
            $costoTotal = $litros * $precioLitro;
            
            RegistroCombustible::create([
                'vehiculo_id' => $faker->randomElement($vehiculos),
                'conductor_id' => $faker->boolean(80) ? $faker->randomElement($conductores) : null,
                'fecha' => $faker->dateTimeBetween('-1 year', 'now'),
                'litros' => $litros,
                'costo_total' => $costoTotal,
                'kilometraje' => $faker->numberBetween(0, 250000),
                'estacion_servicio' => $faker->randomElement($estaciones),
                'factura' => 'FAC-C-' . $faker->bothify('####'),
            ]);
        }
    }
}