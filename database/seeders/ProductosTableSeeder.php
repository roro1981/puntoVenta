<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ProductosTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now()->toDateTimeString();

        $productos = [
            [
                'uuid' => (string) Str::uuid(),
                'codigo' => '0001',
                'descripcion' => 'Pan Marraqueta',
                'precio_compra_neto' => 200.0,
                'precio_compra_bruto' => 238,
                'precio_venta' => 350,
                'stock' => 50.0,
                'stock_minimo' => 5.0,
                'categoria_id' => 3,
                'tipo' => 'P',
                'impuesto1' => 1,
                'impuesto2' => null,
                'imagen' => null,
                'unidad_medida' => 'KG',
                'estado' => 'Activo',
                'fec_creacion' => $now,
                'user_creacion' => 'seeder',
                'fec_modificacion' => null,
                'user_modificacion' => null,
                'fec_eliminacion' => null,
                'user_eliminacion' => null,
            ],
            [
                'uuid' => (string) Str::uuid(),
                'codigo' => '0002',
                'descripcion' => 'Leche Entera 1L',
                'precio_compra_neto' => 600.0,
                'precio_compra_bruto' => 714,
                'precio_venta' => 950,
                'stock' => 40.0,
                'stock_minimo' => 5.0,
                'categoria_id' => 2,
                'tipo' => 'P',
                'impuesto1' => 1,
                'impuesto2' => null,
                'imagen' => null,
                'unidad_medida' => 'L',
                'estado' => 'Activo',
                'fec_creacion' => $now,
                'user_creacion' => 'seeder',
                'fec_modificacion' => null,
                'user_modificacion' => null,
                'fec_eliminacion' => null,
                'user_eliminacion' => null,
            ],
            [
                'uuid' => (string) Str::uuid(),
                'codigo' => '0003',
                'descripcion' => 'Queso Chanco 1kg',
                'precio_compra_neto' => 700.0,
                'precio_compra_bruto' => 833,
                'precio_venta' => 1100,
                'stock' => 30.0,
                'stock_minimo' => 3.0,
                'categoria_id' => 2,
                'tipo' => 'P',
                'impuesto1' => 1,
                'impuesto2' => null,
                'imagen' => null,
                'unidad_medida' => 'KG',
                'estado' => 'Activo',
                'fec_creacion' => $now,
                'user_creacion' => 'seeder',
                'fec_modificacion' => null,
                'user_modificacion' => null,
                'fec_eliminacion' => null,
                'user_eliminacion' => null,
            ],
            [ 'uuid' => (string) Str::uuid(), 'codigo' => '0004', 'descripcion' => 'Arroz 1kg', 'precio_compra_neto' => 700.0, 'precio_compra_bruto' => 833, 'precio_venta' => 1200, 'stock' => 60.0, 'stock_minimo' => 10.0, 'categoria_id' => 3, 'tipo' => 'P', 'impuesto1' => 1, 'impuesto2' => null, 'imagen' => null, 'unidad_medida' => 'KG', 'estado' => 'Activo', 'fec_creacion' => $now, 'user_creacion' => 'seeder', 'fec_modificacion' => null, 'user_modificacion' => null, 'fec_eliminacion' => null, 'user_eliminacion' => null ],
            [ 'uuid' => (string) Str::uuid(), 'codigo' => '0005', 'descripcion' => 'Fideos 500g', 'precio_compra_neto' => 350.0, 'precio_compra_bruto' => 417, 'precio_venta' => 650, 'stock' => 80.0, 'stock_minimo' => 10.0, 'categoria_id' => 3, 'tipo' => 'P', 'impuesto1' => 1, 'impuesto2' => null, 'imagen' => null, 'unidad_medida' => 'KG', 'estado' => 'Activo', 'fec_creacion' => $now, 'user_creacion' => 'seeder', 'fec_modificacion' => null, 'user_modificacion' => null, 'fec_eliminacion' => null, 'user_eliminacion' => null ],
            [ 'uuid' => (string) Str::uuid(), 'codigo' => '0006', 'descripcion' => 'Aceite Girasol 1L', 'precio_compra_neto' => 1200.0, 'precio_compra_bruto' => 1428, 'precio_venta' => 1900, 'stock' => 25.0, 'stock_minimo' => 5.0, 'categoria_id' => 4, 'tipo' => 'P', 'impuesto1' => 1, 'impuesto2' => null, 'imagen' => null, 'unidad_medida' => 'L', 'estado' => 'Activo', 'fec_creacion' => $now, 'user_creacion' => 'seeder', 'fec_modificacion' => null, 'user_modificacion' => null, 'fec_eliminacion' => null, 'user_eliminacion' => null ],
            [ 'uuid' => (string) Str::uuid(), 'codigo' => '0007', 'descripcion' => 'Azúcar 1kg', 'precio_compra_neto' => 450.0, 'precio_compra_bruto' => 535, 'precio_venta' => 800, 'stock' => 40.0, 'stock_minimo' => 5.0, 'categoria_id' => 3, 'tipo' => 'P', 'impuesto1' => 1, 'impuesto2' => null, 'imagen' => null, 'unidad_medida' => 'KG', 'estado' => 'Activo', 'fec_creacion' => $now, 'user_creacion' => 'seeder', 'fec_modificacion' => null, 'user_modificacion' => null, 'fec_eliminacion' => null, 'user_eliminacion' => null ],
            [ 'uuid' => (string) Str::uuid(), 'codigo' => '0008', 'descripcion' => 'Sal 1kg', 'precio_compra_neto' => 200.0, 'precio_compra_bruto' => 238, 'precio_venta' => 350, 'stock' => 50.0, 'stock_minimo' => 5.0, 'categoria_id' => 3, 'tipo' => 'P', 'impuesto1' => 1, 'impuesto2' => null, 'imagen' => null, 'unidad_medida' => 'KG', 'estado' => 'Activo', 'fec_creacion' => $now, 'user_creacion' => 'seeder', 'fec_modificacion' => null, 'user_modificacion' => null, 'fec_eliminacion' => null, 'user_eliminacion' => null ],
            [ 'uuid' => (string) Str::uuid(), 'codigo' => '0009', 'descripcion' => 'Café Instantáneo 250g', 'precio_compra_neto' => 1800.0, 'precio_compra_bruto' => 2142, 'precio_venta' => 2800, 'stock' => 20.0, 'stock_minimo' => 3.0, 'categoria_id' => 5, 'tipo' => 'P', 'impuesto1' => 1, 'impuesto2' => null, 'imagen' => null, 'unidad_medida' => 'KG', 'estado' => 'Activo', 'fec_creacion' => $now, 'user_creacion' => 'seeder', 'fec_modificacion' => null, 'user_modificacion' => null, 'fec_eliminacion' => null, 'user_eliminacion' => null ],
            [ 'uuid' => (string) Str::uuid(), 'codigo' => '0010', 'descripcion' => 'Té Bolsitas 20un', 'precio_compra_neto' => 300.0, 'precio_compra_bruto' => 357, 'precio_venta' => 550, 'stock' => 30.0, 'stock_minimo' => 5.0, 'categoria_id' => 5, 'tipo' => 'P', 'impuesto1' => 1, 'impuesto2' => null, 'imagen' => null, 'unidad_medida' => 'UN', 'estado' => 'Activo', 'fec_creacion' => $now, 'user_creacion' => 'seeder', 'fec_modificacion' => null, 'user_modificacion' => null, 'fec_eliminacion' => null, 'user_eliminacion' => null ],
            [ 'uuid' => (string) Str::uuid(), 'codigo' => '0011', 'descripcion' => 'Galletas Familiares', 'precio_compra_neto' => 600.0, 'precio_compra_bruto' => 714, 'precio_venta' => 1000, 'stock' => 45.0, 'stock_minimo' => 5.0, 'categoria_id' => 6, 'tipo' => 'P', 'impuesto1' => 1, 'impuesto2' => null, 'imagen' => null, 'unidad_medida' => 'UN', 'estado' => 'Activo', 'fec_creacion' => $now, 'user_creacion' => 'seeder', 'fec_modificacion' => null, 'user_modificacion' => null, 'fec_eliminacion' => null, 'user_eliminacion' => null ],
            [ 'uuid' => (string) Str::uuid(), 'codigo' => '0012', 'descripcion' => 'Papas Fritas Bolsa 90g', 'precio_compra_neto' => 450.0, 'precio_compra_bruto' => 535, 'precio_venta' => 850, 'stock' => 70.0, 'stock_minimo' => 10.0, 'categoria_id' => 6, 'tipo' => 'P', 'impuesto1' => 1, 'impuesto2' => null, 'imagen' => null, 'unidad_medida' => 'KG', 'estado' => 'Activo', 'fec_creacion' => $now, 'user_creacion' => 'seeder', 'fec_modificacion' => null, 'user_modificacion' => null, 'fec_eliminacion' => null, 'user_eliminacion' => null ],
            [ 'uuid' => (string) Str::uuid(), 'codigo' => '0013', 'descripcion' => 'Agua Mineral 600ml', 'precio_compra_neto' => 250.0, 'precio_compra_bruto' => 298, 'precio_venta' => 450, 'stock' => 120.0, 'stock_minimo' => 20.0, 'categoria_id' => 7, 'tipo' => 'P', 'impuesto1' => 1, 'impuesto2' => null, 'imagen' => null, 'unidad_medida' => 'L', 'estado' => 'Activo', 'fec_creacion' => $now, 'user_creacion' => 'seeder', 'fec_modificacion' => null, 'user_modificacion' => null, 'fec_eliminacion' => null, 'user_eliminacion' => null ],
            [ 'uuid' => (string) Str::uuid(), 'codigo' => '0014', 'descripcion' => 'Gaseosa 500ml', 'precio_compra_neto' => 500.0, 'precio_compra_bruto' => 595, 'precio_venta' => 900, 'stock' => 90.0, 'stock_minimo' => 10.0, 'categoria_id' => 7, 'tipo' => 'P', 'impuesto1' => 1, 'impuesto2' => null, 'imagen' => null, 'unidad_medida' => 'L', 'estado' => 'Activo', 'fec_creacion' => $now, 'user_creacion' => 'seeder', 'fec_modificacion' => null, 'user_modificacion' => null, 'fec_eliminacion' => null, 'user_eliminacion' => null ],
            [ 'uuid' => (string) Str::uuid(), 'codigo' => '0015', 'descripcion' => 'Cerveza Lata 350ml', 'precio_compra_neto' => 700.0, 'precio_compra_bruto' => 833, 'precio_venta' => 1200, 'stock' => 150.0, 'stock_minimo' => 20.0, 'categoria_id' => 8, 'tipo' => 'P', 'impuesto1' => 1, 'impuesto2' => null, 'imagen' => null, 'unidad_medida' => 'UN', 'estado' => 'Activo', 'fec_creacion' => $now, 'user_creacion' => 'seeder', 'fec_modificacion' => null, 'user_modificacion' => null, 'fec_eliminacion' => null, 'user_eliminacion' => null ],
            [ 'uuid' => (string) Str::uuid(), 'codigo' => '0016', 'descripcion' => 'Vino Tinto 750ml', 'precio_compra_neto' => 2500.0, 'precio_compra_bruto' => 2975, 'precio_venta' => 4000, 'stock' => 40.0, 'stock_minimo' => 5.0, 'categoria_id' => 8, 'tipo' => 'P', 'impuesto1' => 1, 'impuesto2' => null, 'imagen' => null, 'unidad_medida' => 'L', 'estado' => 'Activo', 'fec_creacion' => $now, 'user_creacion' => 'seeder', 'fec_modificacion' => null, 'user_modificacion' => null, 'fec_eliminacion' => null, 'user_eliminacion' => null ],
            [ 'uuid' => (string) Str::uuid(), 'codigo' => '0017', 'descripcion' => 'Ron 700ml', 'precio_compra_neto' => 4000.0, 'precio_compra_bruto' => 4760, 'precio_venta' => 6500, 'stock' => 20.0, 'stock_minimo' => 2.0, 'categoria_id' => 8, 'tipo' => 'P', 'impuesto1' => 1, 'impuesto2' => null, 'imagen' => null, 'unidad_medida' => 'L', 'estado' => 'Activo', 'fec_creacion' => $now, 'user_creacion' => 'seeder', 'fec_modificacion' => null, 'user_modificacion' => null, 'fec_eliminacion' => null, 'user_eliminacion' => null ],
            [ 'uuid' => (string) Str::uuid(), 'codigo' => '0018', 'descripcion' => 'Cigarrillos (paquete)', 'precio_compra_neto' => 1800.0, 'precio_compra_bruto' => 2142, 'precio_venta' => 3000, 'stock' => 60.0, 'stock_minimo' => 5.0, 'categoria_id' => 9, 'tipo' => 'P', 'impuesto1' => 1, 'impuesto2' => null, 'imagen' => null, 'unidad_medida' => 'UN', 'estado' => 'Activo', 'fec_creacion' => $now, 'user_creacion' => 'seeder', 'fec_modificacion' => null, 'user_modificacion' => null, 'fec_eliminacion' => null, 'user_eliminacion' => null ],
            [ 'uuid' => (string) Str::uuid(), 'codigo' => '0019', 'descripcion' => 'Jabón de manos 250ml', 'precio_compra_neto' => 400.0, 'precio_compra_bruto' => 476, 'precio_venta' => 750, 'stock' => 35.0, 'stock_minimo' => 5.0, 'categoria_id' => 10, 'tipo' => 'P', 'impuesto1' => 1, 'impuesto2' => null, 'imagen' => null, 'unidad_medida' => 'L', 'estado' => 'Activo', 'fec_creacion' => $now, 'user_creacion' => 'seeder', 'fec_modificacion' => null, 'user_modificacion' => null, 'fec_eliminacion' => null, 'user_eliminacion' => null ],
            [ 'uuid' => (string) Str::uuid(), 'codigo' => '0020', 'descripcion' => 'Detergente 1L', 'precio_compra_neto' => 1500.0, 'precio_compra_bruto' => 1785, 'precio_venta' => 2400, 'stock' => 25.0, 'stock_minimo' => 5.0, 'categoria_id' => 10, 'tipo' => 'P', 'impuesto1' => 1, 'impuesto2' => null, 'imagen' => null, 'unidad_medida' => 'L', 'estado' => 'Activo', 'fec_creacion' => $now, 'user_creacion' => 'seeder', 'fec_modificacion' => null, 'user_modificacion' => null, 'fec_eliminacion' => null, 'user_eliminacion' => null ],
            [ 'uuid' => (string) Str::uuid(), 'codigo' => '0021', 'descripcion' => 'Papel Higiénico 4un', 'precio_compra_neto' => 900.0, 'precio_compra_bruto' => 1071, 'precio_venta' => 1500, 'stock' => 40.0, 'stock_minimo' => 5.0, 'categoria_id' => 10, 'tipo' => 'P', 'impuesto1' => 1, 'impuesto2' => null, 'imagen' => null, 'unidad_medida' => 'UN', 'estado' => 'Activo', 'fec_creacion' => $now, 'user_creacion' => 'seeder', 'fec_modificacion' => null, 'user_modificacion' => null, 'fec_eliminacion' => null, 'user_eliminacion' => null ],
            [ 'uuid' => (string) Str::uuid(), 'codigo' => '0022', 'descripcion' => 'Pañales Talla M 20un', 'precio_compra_neto' => 5000.0, 'precio_compra_bruto' => 5950, 'precio_venta' => 7500, 'stock' => 15.0, 'stock_minimo' => 2.0, 'categoria_id' => 11, 'tipo' => 'P', 'impuesto1' => 1, 'impuesto2' => null, 'imagen' => null, 'unidad_medida' => 'UN', 'estado' => 'Activo', 'fec_creacion' => $now, 'user_creacion' => 'seeder', 'fec_modificacion' => null, 'user_modificacion' => null, 'fec_eliminacion' => null, 'user_eliminacion' => null ],
            [ 'uuid' => (string) Str::uuid(), 'codigo' => '0023', 'descripcion' => 'Caramelos Surtidos 1kg', 'precio_compra_neto' => 1200.0, 'precio_compra_bruto' => 1428, 'precio_venta' => 2000, 'stock' => 30.0, 'stock_minimo' => 5.0, 'categoria_id' => 6, 'tipo' => 'P', 'impuesto1' => 1, 'impuesto2' => null, 'imagen' => null, 'unidad_medida' => 'KG', 'estado' => 'Activo', 'fec_creacion' => $now, 'user_creacion' => 'seeder', 'fec_modificacion' => null, 'user_modificacion' => null, 'fec_eliminacion' => null, 'user_eliminacion' => null ],
            [ 'uuid' => (string) Str::uuid(), 'codigo' => '0024', 'descripcion' => 'Baterías AA Pack 4', 'precio_compra_neto' => 800.0, 'precio_compra_bruto' => 952, 'precio_venta' => 1400, 'stock' => 60.0, 'stock_minimo' => 10.0, 'categoria_id' => 12, 'tipo' => 'P', 'impuesto1' => 1, 'impuesto2' => null, 'imagen' => null, 'unidad_medida' => 'UN', 'estado' => 'Activo', 'fec_creacion' => $now, 'user_creacion' => 'seeder', 'fec_modificacion' => null, 'user_modificacion' => null, 'fec_eliminacion' => null, 'user_eliminacion' => null ],
            [ 'uuid' => (string) Str::uuid(), 'codigo' => '0025', 'descripcion' => 'Chocolates Caja', 'precio_compra_neto' => 1200.0, 'precio_compra_bruto' => 1428, 'precio_venta' => 2000, 'stock' => 35.0, 'stock_minimo' => 5.0, 'categoria_id' => 6, 'tipo' => 'P', 'impuesto1' => 1, 'impuesto2' => null, 'imagen' => null, 'unidad_medida' => 'CJ', 'estado' => 'Activo', 'fec_creacion' => $now, 'user_creacion' => 'seeder', 'fec_modificacion' => null, 'user_modificacion' => null, 'fec_eliminacion' => null, 'user_eliminacion' => null ],
            [ 'uuid' => (string) Str::uuid(), 'codigo' => '0026', 'descripcion' => 'Salsa Tomate 350g', 'precio_compra_neto' => 450.0, 'precio_compra_bruto' => 535, 'precio_venta' => 800, 'stock' => 50.0, 'stock_minimo' => 5.0, 'categoria_id' => 3, 'tipo' => 'P', 'impuesto1' => 1, 'impuesto2' => null, 'imagen' => null, 'unidad_medida' => 'KG', 'estado' => 'Activo', 'fec_creacion' => $now, 'user_creacion' => 'seeder', 'fec_modificacion' => null, 'user_modificacion' => null, 'fec_eliminacion' => null, 'user_eliminacion' => null ],
            [ 'uuid' => (string) Str::uuid(), 'codigo' => '0027', 'descripcion' => 'Enlatados Atún 160g', 'precio_compra_neto' => 900.0, 'precio_compra_bruto' => 1071, 'precio_venta' => 1500, 'stock' => 70.0, 'stock_minimo' => 10.0, 'categoria_id' => 3, 'tipo' => 'P', 'impuesto1' => 1, 'impuesto2' => null, 'imagen' => null, 'unidad_medida' => 'KG', 'estado' => 'Activo', 'fec_creacion' => $now, 'user_creacion' => 'seeder', 'fec_modificacion' => null, 'user_modificacion' => null, 'fec_eliminacion' => null, 'user_eliminacion' => null ],
            [ 'uuid' => (string) Str::uuid(), 'codigo' => '0028', 'descripcion' => 'Condones Caja', 'precio_compra_neto' => 2000.0, 'precio_compra_bruto' => 2380, 'precio_venta' => 3000, 'stock' => 15.0, 'stock_minimo' => 2.0, 'categoria_id' => 13, 'tipo' => 'P', 'impuesto1' => 1, 'impuesto2' => null, 'imagen' => null, 'unidad_medida' => 'CJ', 'estado' => 'Activo', 'fec_creacion' => $now, 'user_creacion' => 'seeder', 'fec_modificacion' => null, 'user_modificacion' => null, 'fec_eliminacion' => null, 'user_eliminacion' => null ],
            [ 'uuid' => (string) Str::uuid(), 'codigo' => '0029', 'descripcion' => 'Soplador/Mechero', 'precio_compra_neto' => 500.0, 'precio_compra_bruto' => 595, 'precio_venta' => 900, 'stock' => 25.0, 'stock_minimo' => 5.0, 'categoria_id' => 12, 'tipo' => 'P', 'impuesto1' => 1, 'impuesto2' => null, 'imagen' => null, 'unidad_medida' => 'UN', 'estado' => 'Activo', 'fec_creacion' => $now, 'user_creacion' => 'seeder', 'fec_modificacion' => null, 'user_modificacion' => null, 'fec_eliminacion' => null, 'user_eliminacion' => null ],
            [ 'uuid' => (string) Str::uuid(), 'codigo' => '0030', 'descripcion' => 'Bolsas Plástico 30un', 'precio_compra_neto' => 300.0, 'precio_compra_bruto' => 357, 'precio_venta' => 600, 'stock' => 100.0, 'stock_minimo' => 10.0, 'categoria_id' => 12, 'tipo' => 'P', 'impuesto1' => 1, 'impuesto2' => null, 'imagen' => null, 'unidad_medida' => 'UN', 'estado' => 'Activo', 'fec_creacion' => $now, 'user_creacion' => 'seeder', 'fec_modificacion' => null, 'user_modificacion' => null, 'fec_eliminacion' => null, 'user_eliminacion' => null ]
        ];

        DB::table('productos')->insert($productos);
    }
}
