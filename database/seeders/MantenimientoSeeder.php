<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Mantenimiento;
use App\Models\Vehiculo;
use Faker\Factory as Faker;


class MantenimientoSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('es_ES');
        
        $vehiculos = Vehiculo::pluck('id')->toArray();
        $tipos = ['preventivo', 'correctivo'];
        $talleres = ['Talleres El Motor', 'Mecánica Rápida', 'Servicio Autorizado', 'Taller Central', 'Diesel Service'];
        
        for ($i = 0; $i < 25; $i++) {
            $realizado = $faker->boolean(70); // 70% realizados
            
            Mantenimiento::create([
                'vehiculo_id' => $faker->randomElement($vehiculos),
                'fecha_programada' => $faker->dateTimeBetween('-6 months', '+6 months'),
                'fecha_realizada' => $realizado ? $faker->dateTimeBetween('-6 months', 'now') : null,
                'tipo' => $faker->randomElement($tipos),
                'costo' => $faker->randomFloat(2, 100, 2000),
                'taller' => $faker->randomElement($talleres),
                'descripcion' => $faker->sentence(),
                'kilometraje_mantenimiento' => $faker->numberBetween(5000, 250000),
                'factura' => $realizado ? 'FAC-' . $faker->bothify('####') : null,
            ]);
        }
    }
}