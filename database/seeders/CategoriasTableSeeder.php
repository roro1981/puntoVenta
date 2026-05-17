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
            ['descripcion_categoria' => 'INSUMOS', 'estado_categoria' => 1],
            ['descripcion_categoria' => 'A BASE DE GIN', 'estado_categoria' => 1],
            ['descripcion_categoria' => 'A BASE DE RON', 'estado_categoria' => 1],
            ['descripcion_categoria' => 'A BASE DE VODKA', 'estado_categoria' => 1],
            ['descripcion_categoria' => 'CAFETERIA', 'estado_categoria' => 1],
            ['descripcion_categoria' => 'ENSALADAS', 'estado_categoria' => 1],
            ['descripcion_categoria' => 'ENTRADAS', 'estado_categoria' => 1],
            ['descripcion_categoria' => 'ESPUMANTES', 'estado_categoria' => 1],
            ['descripcion_categoria' => 'FONDOS', 'estado_categoria' => 1],
            ['descripcion_categoria' => 'GIN', 'estado_categoria' => 1],
            ['descripcion_categoria' => 'HAMBURGUESAS', 'estado_categoria' => 1],
            ['descripcion_categoria' => 'INFALTABLES', 'estado_categoria' => 1],
            ['descripcion_categoria' => 'MENU DE NINOS', 'estado_categoria' => 1],
            ['descripcion_categoria' => 'OTROS', 'estado_categoria' => 1],
            ['descripcion_categoria' => 'PARA COMPARTIR', 'estado_categoria' => 1],
            ['descripcion_categoria' => 'PISCOS', 'estado_categoria' => 1],
            ['descripcion_categoria' => 'POSTRES Y PASTELERIA', 'estado_categoria' => 1],
            ['descripcion_categoria' => 'RON', 'estado_categoria' => 1],
            ['descripcion_categoria' => 'SHOP', 'estado_categoria' => 1],
            ['descripcion_categoria' => 'SIN ALCOHOL', 'estado_categoria' => 1],
            ['descripcion_categoria' => 'SOURS', 'estado_categoria' => 1],
            ['descripcion_categoria' => 'SPRITZ', 'estado_categoria' => 1],
            ['descripcion_categoria' => 'VARIOS', 'estado_categoria' => 1],
            ['descripcion_categoria' => 'VINOS', 'estado_categoria' => 1],
            ['descripcion_categoria' => 'VODKA', 'estado_categoria' => 1],
            ['descripcion_categoria' => 'WHISKY', 'estado_categoria' => 1],
        ];

        DB::table('categorias')->insert($categorias);
    }
}
