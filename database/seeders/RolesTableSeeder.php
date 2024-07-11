<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('roles')->insert([
            ['role_name' => 'Administrador', 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_name' => 'Usuario', 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_name' => 'SuperAdministrador', 'created_at' => Carbon::now()->toDateTimeString()],
            // Añade aquí más menús según lo necesites
        ]);
    }
}
