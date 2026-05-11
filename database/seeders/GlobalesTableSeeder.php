<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GlobalesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rows = [
            ['nom_var' => 'STOCK_NEGATIVO',      'valor_var' => '0',          'descrip_var' => 'Permite o no stock negativo de productos 0:NO y 1:SI'],
            ['nom_var' => 'TIPO_NEGOCIO',         'valor_var' => 'RESTAURANT', 'descrip_var' => 'Determina si el negocio es almacen, almacen_preventa o restaurant'],
            ['nom_var' => 'PORCENTAJE_PROPINA',   'valor_var' => '10',         'descrip_var' => 'Porcentaje de propina sugerida para comandas'],
            ['nom_var' => 'RESERVA_EXPIRACION_MESA_MINUTOS', 'valor_var' => '15', 'descrip_var' => 'Minutos de inactividad para expirar una reserva de mesa en restaurant'],
            ['nom_var' => 'SISTEMA_ACTIVO',       'valor_var' => '1',          'descrip_var' => 'Activa o desactiva la generacion de nuevas ventas, preventas y comandas. 1: Activo, 0: Suspendido (falta de pago)'],
        ];

        // upsert: inserta si no existe, actualiza descrip_var si ya existe (nom_var es la clave única)
        DB::table('globales')->upsert($rows, ['nom_var'], ['descrip_var']);
    }
}
