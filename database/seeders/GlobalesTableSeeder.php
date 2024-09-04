<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class GlobalesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('globales')->insert([
            ['nom_var' => 'MAX-DESCUENTO', 'valor_var' =>'40', 'descrip_var' =>'Valor maximo de descuento venta'],
            ['nom_var' => 'STOCK_NEGATIVO', 'valor_var' =>'0', 'descrip_var' =>'Permite o no stock negativo de productos 0:NO y 1:SI'],
            ['nom_var' => 'ANCHO_PAPEL', 'valor_var' =>'80', 'descrip_var' =>'Ancho del papel impresora termica '],
           
        ]);
    }
}
