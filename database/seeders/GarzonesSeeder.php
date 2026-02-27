<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GarzonesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('garzones')->insert([
            [
                'nombre' => 'Juan',
                'apellido' => 'Pérez',
                'rut' => '12345678-9',
                'telefono' => '+56912345678',
                'email' => 'juan.perez@restaurant.cl',
                'estado' => 'Activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'María',
                'apellido' => 'González',
                'rut' => '23456789-0',
                'telefono' => '+56923456789',
                'email' => 'maria.gonzalez@restaurant.cl',
                'estado' => 'Activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Carlos',
                'apellido' => 'Rodríguez',
                'rut' => '34567890-1',
                'telefono' => '+56934567890',
                'email' => 'carlos.rodriguez@restaurant.cl',
                'estado' => 'Activo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
