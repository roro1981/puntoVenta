<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SubmenusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('submenus')->insert([
            ['menu_id' => 1, 'submenu_name' => 'Productos', 'submenu_route' => '/productos', 'created_at' => Carbon::now()->toDateTimeString()],
            ['menu_id' => 1, 'submenu_name' => 'Categorías', 'submenu_route' => '/categorias', 'created_at' => Carbon::now()->toDateTimeString()],
            ['menu_id' => 1, 'submenu_name' => 'Crear Recetas', 'submenu_route' => '/recetas_crear', 'created_at' => Carbon::now()->toDateTimeString()],
            ['menu_id' => 1, 'submenu_name' => 'Editar-eliminar recetas', 'submenu_route' => '/recetas', 'created_at' => Carbon::now()->toDateTimeString()],
            ['menu_id' => 1, 'submenu_name' => 'Crear Promociones', 'submenu_route' => '/promociones_crear', 'created_at' => Carbon::now()->toDateTimeString()],
            ['menu_id' => 1, 'submenu_name' => 'Editar-eliminar promociones', 'submenu_route' => '/promociones', 'created_at' => Carbon::now()->toDateTimeString()],
            ['menu_id' => 1, 'submenu_name' => 'Precio según cantidad', 'submenu_route' => '/precio_segun_cant', 'created_at' => Carbon::now()->toDateTimeString()],
            ['menu_id' => 2, 'submenu_name' => 'Compras', 'submenu_route' => '/ingresos', 'created_at' => Carbon::now()->toDateTimeString()],
            ['menu_id' => 2, 'submenu_name' => 'Proveedores', 'submenu_route' => '/proveedores', 'created_at' => Carbon::now()->toDateTimeString()],
            ['menu_id' => 2, 'submenu_name' => 'Entradas y salidas', 'submenu_route' => '/ent_sal', 'created_at' => Carbon::now()->toDateTimeString()],
            ['menu_id' => 3, 'submenu_name' => 'Generar ventas', 'submenu_route' => '/generar_ventas', 'created_at' => Carbon::now()->toDateTimeString()],
            ['menu_id' => 3, 'submenu_name' => 'Tickets emitidos', 'submenu_route' => '/tickets_emitidos', 'created_at' => Carbon::now()->toDateTimeString()],
            ['menu_id' => 3, 'submenu_name' => 'Cierres de caja', 'submenu_route' => '/cierres_caja', 'created_at' => Carbon::now()->toDateTimeString()],
            ['menu_id' => 4, 'submenu_name' => 'Movimientos de productos', 'submenu_route' => '/mov_productos', 'created_at' => Carbon::now()->toDateTimeString()],
            ['menu_id' => 4, 'submenu_name' => 'Ventas por fecha', 'submenu_route' => '/vtas_fecha', 'created_at' => Carbon::now()->toDateTimeString()],
            ['menu_id' => 4, 'submenu_name' => 'Productos más vendidos', 'submenu_route' => '/prods_mas_vendidos', 'created_at' => Carbon::now()->toDateTimeString()],
            ['menu_id' => 4, 'submenu_name' => 'Productos más rentables', 'submenu_route' => '/prods_rentables', 'created_at' => Carbon::now()->toDateTimeString()],
            ['menu_id' => 4, 'submenu_name' => 'Categorias más vendidas', 'submenu_route' => '/cat_mas_vendidas', 'created_at' => Carbon::now()->toDateTimeString()],
            ['menu_id' => 4, 'submenu_name' => 'Historial precio producto', 'submenu_route' => '/hist_precio_prod', 'created_at' => Carbon::now()->toDateTimeString()],
            ['menu_id' => 4, 'submenu_name' => 'Inventario', 'submenu_route' => '/inventario', 'created_at' => Carbon::now()->toDateTimeString()],
            ['menu_id' => 5, 'submenu_name' => 'Usuarios', 'submenu_route' => '/usuarios', 'created_at' => Carbon::now()->toDateTimeString()],
            ['menu_id' => 5, 'submenu_name' => 'Roles', 'submenu_route' => '/roles', 'created_at' => Carbon::now()->toDateTimeString()],
            ['menu_id' => 5, 'submenu_name' => 'Permisos', 'submenu_route' => '/permisos', 'created_at' => Carbon::now()->toDateTimeString()],
            ['menu_id' => 6, 'submenu_name' => 'Datos corporativos', 'submenu_route' => '/datos_corp', 'created_at' => Carbon::now()->toDateTimeString()],
            ['menu_id' => 6, 'submenu_name' => 'Datos globales', 'submenu_route' => '/datos_glob', 'created_at' => Carbon::now()->toDateTimeString()],
            ['menu_id' => 6, 'submenu_name' => 'Impuestos', 'submenu_route' => '/impuestos', 'created_at' => Carbon::now()->toDateTimeString()],
            ['menu_id' => 7, 'submenu_name' => 'Categorías eliminadas', 'submenu_route' => '/cats_elim', 'created_at' => Carbon::now()->toDateTimeString()],
            ['menu_id' => 7, 'submenu_name' => 'Clientes eliminados', 'submenu_route' => '/clientes_elim', 'created_at' => Carbon::now()->toDateTimeString()],
            ['menu_id' => 7, 'submenu_name' => 'Productos eliminados', 'submenu_route' => '/prods_elim', 'created_at' => Carbon::now()->toDateTimeString()],
            ['menu_id' => 7, 'submenu_name' => 'Recetas eliminadas', 'submenu_route' => '/recetas_elim', 'created_at' => Carbon::now()->toDateTimeString()],
            ['menu_id' => 7, 'submenu_name' => 'Promociones eliminadas', 'submenu_route' => '/promos_elim', 'created_at' => Carbon::now()->toDateTimeString()],
            ['menu_id' => 7, 'submenu_name' => 'Proveedores eliminados', 'submenu_route' => '/provs_elim', 'created_at' => Carbon::now()->toDateTimeString()],
        ]);
    }
}
