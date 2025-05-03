<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('regiones')->insert([
            ['nom_region' => 'Región de Arica y Parinacota'],
            ['nom_region' => 'Región de Tarapacá'],
            ['nom_region' => 'Región de Antofagasta'],
            ['nom_region' => 'Región de Atacama'],
            ['nom_region' => 'Región de Coquimbo'],
            ['nom_region' => 'Región de Valparaíso'],
            ['nom_region' => 'Región Metropolitana de Santiago'],
            ['nom_region' => 'Región del Libertador General Bernardo O’Higgins'],
            ['nom_region' => 'Región del Maule'],
            ['nom_region' => 'Región de Ñuble'],
            ['nom_region' => 'Región del Biobío'],
            ['nom_region' => 'Región de La Araucanía'],
            ['nom_region' => 'Región de Los Ríos'],
            ['nom_region' => 'Región de Los Lagos'],
            ['nom_region' => 'Región de Aysén del General Carlos Ibáñez del Campo'],
            ['nom_region' => 'Región de Magallanes y de la Antártica Chilena']
        ]);
    }
}
