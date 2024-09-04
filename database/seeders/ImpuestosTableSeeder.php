<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ImpuestosTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('impuestos')->insert([
            ['nom_imp' => "IVA", 'valor_imp' => 19, 'last_activity' => Carbon::now()->toDateTimeString(),
             'descrip_imp' =>'Impuesto a las Ventas y Servicios '],
            ['nom_imp' => "LICORES", 'valor_imp' => 31.5, 'last_activity' => Carbon::now()->toDateTimeString(),
             'descrip_imp' =>'Impuesto adicional al IVA a licores, piscos, whisky, aguardientes y destilados, incluyendo 
              los vinos licorosos o aromatizados similares al vermouth'],  
            ['nom_imp' => "VINOS-CERVEZAS", 'valor_imp' => 20.5, 'last_activity' => Carbon::now()->toDateTimeString(),
             'descrip_imp' =>'Impuesto adicional al IVA a vinos destinados al consumo, comprendidos los vinos gasificados, los espumosos 
             o champaña, los generosos o asoleados, chichas y sidras destinadas al consumo, cualquiera que sea 
             su envase, cervezas y otras bebidas alcohólicas, cualquiera que sea su tipo, calidad o denominación'],  
            ['nom_imp' => "BEBIDAS-AZUCARADAS", 'valor_imp' => 18, 'last_activity' => Carbon::now()->toDateTimeString(),
            'descrip_imp' =>'Impuesto adicional al IVA a las bebidas que, por unidad de peso o volumen, o por porción de consumo, 
             presenten en su composición nutricional elevados contenidos de calorías, grasas, azúcares'],
            ['nom_imp' => "BEBIDAS-NO-AZUCARADAS", 'valor_imp' => 10, 'last_activity' => Carbon::now()->toDateTimeString(),
             'descrip_imp' =>'Impuesto adicional al IVA a Bebidas analcohólicas naturales o artificiales, energizantes o hipertónicas, jarabes 
             y en general cualquier otro producto que las sustituya o que sirva para preparar bebidas similares, y aguas 
             minerales o termales a las cuales se les haya adicionado colorante, sabor o edulcorantes'],
            ['nom_imp' => "CARNES", 'valor_imp' => 5, 'last_activity' => Carbon::now()->toDateTimeString(),
            'descrip_imp' =>'Impuesto adicional al IVA a carnes y prestación de servicios de faenamiento'],
            ['nom_imp' => "HARINAS", 'valor_imp' => 12, 'last_activity' => Carbon::now()->toDateTimeString(),
            'descrip_imp' =>'Impuesto adicional al IVA a productos de harina de trigo o mezcla completa - premezcla'],
        ]);
    }
}
