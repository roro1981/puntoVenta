<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class MenuRolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('menu_roles')->insert([
            //Administrador
            ['role_id' => 1, 'submenu_id' => 1, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 1, 'submenu_id' => 2, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 1, 'submenu_id' => 3, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 1, 'submenu_id' => 4, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 1, 'submenu_id' => 5, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 1, 'submenu_id' => 6, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 1, 'submenu_id' => 7, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 1, 'submenu_id' => 8, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 1, 'submenu_id' => 9, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 1, 'submenu_id' => 10 , 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 1, 'submenu_id' => 11, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 1, 'submenu_id' => 12, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 1, 'submenu_id' => 13, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 1, 'submenu_id' => 14, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 1, 'submenu_id' => 15, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 1, 'submenu_id' => 16, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 1, 'submenu_id' => 17, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 1, 'submenu_id' => 18, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 1, 'submenu_id' => 19, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 1, 'submenu_id' => 20, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 1, 'submenu_id' => 21, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 1, 'submenu_id' => 22, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 1, 'submenu_id' => 23, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 1, 'submenu_id' => 24, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 1, 'submenu_id' => 25, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 1, 'submenu_id' => 26, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 1, 'submenu_id' => 27, 'created_at' => Carbon::now()->toDateTimeString()],
            //usuario
            ['role_id' => 2, 'submenu_id' => 11, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 2, 'submenu_id' => 12, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 2, 'submenu_id' => 13, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 2, 'submenu_id' => 14, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 2, 'submenu_id' => 15, 'created_at' => Carbon::now()->toDateTimeString()],
            //Superadministrador
            ['role_id' => 3, 'submenu_id' => 1, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 3, 'submenu_id' => 2, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 3, 'submenu_id' => 3, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 3, 'submenu_id' => 4, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 3, 'submenu_id' => 5, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 3, 'submenu_id' => 6, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 3, 'submenu_id' => 7, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 3, 'submenu_id' => 8, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 3, 'submenu_id' => 9, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 3, 'submenu_id' => 10 , 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 3, 'submenu_id' => 11, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 3, 'submenu_id' => 12, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 3, 'submenu_id' => 13, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 3, 'submenu_id' => 14, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 3, 'submenu_id' => 15, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 3, 'submenu_id' => 16, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 3, 'submenu_id' => 17, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 3, 'submenu_id' => 18, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 3, 'submenu_id' => 19, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 3, 'submenu_id' => 20, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 3, 'submenu_id' => 21, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 3, 'submenu_id' => 22, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 3, 'submenu_id' => 23, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 3, 'submenu_id' => 24, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 3, 'submenu_id' => 25, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 3, 'submenu_id' => 26, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 3, 'submenu_id' => 27, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 3, 'submenu_id' => 28, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 3, 'submenu_id' => 29, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 3, 'submenu_id' => 30, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 3, 'submenu_id' => 31, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 3, 'submenu_id' => 32, 'created_at' => Carbon::now()->toDateTimeString()],
            ['role_id' => 3, 'submenu_id' => 33, 'created_at' => Carbon::now()->toDateTimeString()]
        ]);
    }
}
