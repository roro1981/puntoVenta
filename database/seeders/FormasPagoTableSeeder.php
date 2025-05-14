<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class FormasPagoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $formasPago = [
            ['descripcion_pago' => 'CONTADO'],
            ['descripcion_pago' => 'CHEQUE AL DIA'],
            ['descripcion_pago' => 'CHEQUE A FECHA'],
            ['descripcion_pago' => 'CREDITO A X DIAS'],
        ];

        DB::table('formas_pago')->insert($formasPago);
    }
}
