<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class MesasTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mesas = [];
        for ($i = 1; $i <= 10; $i++) {
            $mesas[] = [
                'nombre' => 'Mesa ' . $i,
                'orden' => $i,
                'capacidad' => ($i <= 4) ? 4 : (($i <= 8) ? 6 : 8),
                'activa' => true,
                'created_at' => Carbon::now()->toDateTimeString(),
                'updated_at' => Carbon::now()->toDateTimeString()
            ];
        }

        DB::table('mesas')->insert($mesas);
    }
}
