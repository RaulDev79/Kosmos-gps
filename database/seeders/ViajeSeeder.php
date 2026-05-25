<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Viaje;
use App\Models\Vehiculo;
use App\Models\Conductor;
use Faker\Factory as Faker;


class ViajeSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('es_ES');
        
        $vehiculos = Vehiculo::pluck('id')->toArray();
        $conductores = Conductor::pluck('id')->toArray();
        $estados = ['planificado', 'en_curso', 'completado', 'cancelado'];
        $propositos = [
            'Entrega de mercancía', 'Transporte de pasajeros', 
            'Mudanza', 'Mantenimiento externo', 'Recolección',
            'Distribución local', 'Ruta larga distancia'
        ];
        
        for ($i = 0; $i < 25; $i++) {
            $kmInicio = $faker->numberBetween(0, 200000);
            $kmFin = $kmInicio + $faker->numberBetween(50, 2000);
            $estado = $faker->randomElement($estados);
            
            Viaje::create([
                'vehiculo_id' => $faker->randomElement($vehiculos),
                'conductor_id' => $faker->randomElement($conductores),
                'fecha_hora_inicio' => $faker->dateTimeBetween('-1 year', 'now'),
                'fecha_hora_fin' => $estado == 'completado' ? $faker->dateTimeBetween('-1 year', 'now') : null,
                'kilometraje_inicio' => $kmInicio,
                'kilometraje_fin' => $estado == 'completado' ? $kmFin : null,
                'proposito' => $faker->randomElement($propositos),
                'estado' => $estado,
            ]);
        }
    }
}