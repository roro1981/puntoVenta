<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InsumosSeeder extends Seeder
{
    /**
     * Retorna la unidad de medida según el nombre del producto.
     * Ajusta esta lógica según tus criterios.
     */
    private function getUnidadMedida($ingredient)
    {
        $lower = strtolower($ingredient);

        // Si aparece algo vinculado a carnes, pescados o mariscos
        if (
            str_contains($lower, 'carne') ||
            str_contains($lower, 'pollo') ||
            str_contains($lower, 'cerdo') ||
            str_contains($lower, 'chancho') ||
            str_contains($lower, 'pescado') ||
            str_contains($lower, 'merluza') ||
            str_contains($lower, 'reineta') ||
            str_contains($lower, 'salmón') ||
            str_contains($lower, 'costilla') ||
            str_contains($lower, 'marisco') ||
            str_contains($lower, 'chunchules') ||
            str_contains($lower, 'mollejas') ||
            str_contains($lower, 'prieta') ||
            str_contains($lower, 'pulpo') ||
            str_contains($lower, 'choritos') ||
            str_contains($lower, 'almejas') ||
            str_contains($lower, 'camarones') ||
            str_contains($lower, 'salsa') ||
            str_contains($lower, 'mostaza') ||
            str_contains($lower, 'ketchup') ||
            str_contains($lower, 'mayonesa') ||
            str_contains($lower, 'sal ') ||
            str_contains($lower, 'ostiones')
        ) {
            return 'KG';  // Se asume venta/peso en kilogramos
        }

        // Si aparece algo vinculado a líquidos (aceites, salsas, lácteos líquidos, vinagres)
        if (
            str_contains($lower, 'aceite') ||
            str_contains($lower, 'vinagre') ||
            str_contains($lower, 'leche') ||
            str_contains($lower, 'crema') ||
            str_contains($lower, 'bebida')
        ) {
            return 'L';   // Se asume venta/medida en litros
        }

        // Por defecto, se maneja por unidad
        return 'UN';
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
