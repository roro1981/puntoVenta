<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class MenusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('menus')->insert([
            ['menu_name' => 'Almacén', 'menu_route' => '/almacen', 'menu_fa' => 'fa fa-truck' ,'created_at' => Carbon::now()->toDateTimeString()],
            ['menu_name' => 'Ventas', 'menu_route' => '/ventas', 'menu_fa' => 'fa fa-shopping-cart', 'created_at' => Carbon::now()->toDateTimeString()],
            ['menu_name' => 'Compras', 'menu_route' => '/compras', 'menu_fa' => 'fa fa-th', 'created_at' => Carbon::now()->toDateTimeString()],
            ['menu_name' => 'Reportes', 'menu_route' => '/reportes', 'menu_fa' => 'fa fa-bar-chart', 'created_at' => Carbon::now()->toDateTimeString()],
            ['menu_name' => 'Usuarios', 'menu_route' => '/usuarios', 'menu_fa' => 'fa fa-user', 'created_at' => Carbon::now()->toDateTimeString()],
            ['menu_name' => 'Configuración', 'menu_route' => '/configuracion', 'menu_fa' => 'fa fa-cogs', 'created_at' => Carbon::now()->toDateTimeString()],
            ['menu_name' => 'Re-activaciones', 'menu_route' => '/reactivaciones', 'menu_fa' => 'fa fa-recycle', 'created_at' => Carbon::now()->toDateTimeString()]
        ]);
    }
}
