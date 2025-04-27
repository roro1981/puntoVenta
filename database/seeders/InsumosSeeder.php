<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InsumosSeeder extends Seeder
{
    /**
     * Retorna la unidad de medida según el nombre del producto.
     * Ajusta esta lógica según tus criterios.
     */
    private function getUnidadMedida($ingredient)
    {
        $lower = strtolower($ingredient);

        // Si el nombre contiene algo relacionado a líquidos, usar LT
        if (
            str_contains($lower, 'aceite') ||
            str_contains($lower, 'vinagre') ||
            str_contains($lower, 'leche') ||
            str_contains($lower, 'crema') ||
            str_contains($lower, 'bebida') ||
            str_contains($lower, 'salsa') ||
            str_contains($lower, 'mostaza') ||
            str_contains($lower, 'ketchup') ||
            str_contains($lower, 'mayonesa') ||
            str_contains($lower, 'soya') ||
            str_contains($lower, 'miel')
        ) {
            return 'LT';
        }

        return 'KG';
    }

    public function run()
    {
        // 100 insumos típicos de cocina chilena
        $chileanIngredients = [
            'Carne de vacuno',
            'Carne de cerdo',
            'Carne de pollo',
            'Carne de pavo',
            'Longaniza',
            'Chorizo',
            'Chunchules',
            'Mollejas',
            'Costillar de cerdo',
            'Prietas',
            'Merluza',
            'Reineta',
            'Salmón',
            'Mariscos mixtos',
            'Almejas',
            'Choritos',
            'Camarones',
            'Ostiones',
            'Pulpo',
            'Jurel',
            'Papa chilota',
            'Papa normal',
            'Zapallo camote',
            'Zapallo italiano',
            'Cebolla',
            'Cebollín',
            'Zanahoria',
            'Pimentón rojo',
            'Pimentón verde',
            'Tomate',
            'Lechuga escarola',
            'Lechuga romana',
            'Choclo pastelero',
            'Porotos verdes',
            'Arvejas',
            'Habas',
            'Coliflor',
            'Brócoli',
            'Nabo',
            'Berenjena',
            'Ajo',
            'Cilantro',
            'Perejil',
            'Orégano fresco',
            'Orégano seco',
            'Comino molido',
            'Merquén',
            'Ají de color',
            'Laurel',
            'Romero',
            'Tomillo',
            'Aceite de maravilla',
            'Aceite de oliva',
            'Sal de mar',
            'Pimienta negra',
            'Vinagre de manzana',
            'Vinagre de vino',
            'Mostaza',
            'Ketchup',
            'Mayonesa casera',
            'Salsa de soya',
            'Azúcar granulada',
            'Pimienta blanca',
            'Polvo de hornear',
            'Bicarbonato de sodio',
            'Mantequilla',
            'Margarina',
            'Leche entera',
            'Crema de leche',
            'Queso mantecoso',
            'Queso chanco',
            'Queso fresco',
            'Queso gauda',
            'Queso rallado',
            'Huevo de gallina',
            'Huevo de codorniz',
            'Lentejas',
            'Garbanzos',
            'Porotos granados',
            'Porotos negros',
            'Arroz largo',
            'Arroz grano corto',
            'Fideos espagueti',
            'Fideos tallarines',
            'Fideos corbatitas',
            'Harina de trigo',
            'Harina integral',
            'Harina de maíz',
            'Pan rallado',
            'Avena',
            'Trigo mote',
            'Chuchoca',
            'Polenta',
            'Sémola',
            'Quinoa',
            'Miel',
            'Manjar',
            'Chocolate de repostería',
            'Maní tostado',
            'Nueces',
            'Pasas',
            'Almendras',
            'Castañas',
            'Huesillos',
            'Duraznos',
            'Ciruelas deshidratadas',
            'Manzanas',
            'Limones',
            'Naranjas',
            'Plátanos'
        ];

        $productosData = [];
        $codigoActual = 100;

        foreach ($chileanIngredients as $ingredient) {
            $productosData[] = [
                'uuid'                 => Str::uuid(),
                'codigo'               => $codigoActual++,
                'descripcion'          => $ingredient,
                'precio_compra_neto'   => 1,
                'precio_compra_bruto'  => 1,
                'precio_venta'         => 1,
                'stock'                => 0,
                'stock_minimo'         => 0,
                'categoria_id'         => 1,
                'tipo'                 => 'I',
                'impuesto1'            => 19,
                'estado'               => 'Activo',
                'fec_creacion'         => Carbon::now(),
                'user_creacion'        => 'roro1981',
                'unidad_medida'        => $this->getUnidadMedida($ingredient),
            ];
        }

        DB::table('productos')->insert($productosData);
    }
}
