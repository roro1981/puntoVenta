<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CategoriasTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categorias = [
            ['descripcion_categoria' => 'Insumos', 'estado_categoria' => 1],
            ['descripcion_categoria' => 'Lácteos y Huevos', 'estado_categoria' => 1],
            ['descripcion_categoria' => 'Abarrotes', 'estado_categoria' => 1],
            ['descripcion_categoria' => 'Aceites y Condimentos', 'estado_categoria' => 1],
            ['descripcion_categoria' => 'Bebidas Calientes', 'estado_categoria' => 1],
            ['descripcion_categoria' => 'Snacks y Galletas', 'estado_categoria' => 1],
            ['descripcion_categoria' => 'Bebidas', 'estado_categoria' => 1],
            ['descripcion_categoria' => 'Licores y Vinos', 'estado_categoria' => 1],
            ['descripcion_categoria' => 'Tabaco', 'estado_categoria' => 1],
            ['descripcion_categoria' => 'Higiene y Limpieza', 'estado_categoria' => 1],
            ['descripcion_categoria' => 'Bebés', 'estado_categoria' => 1],
            ['descripcion_categoria' => 'Variedades', 'estado_categoria' => 1],
            ['descripcion_categoria' => 'Salud Sexual', 'estado_categoria' => 1]
        ];

        DB::table('categorias')->insert($categorias);
    }
}
