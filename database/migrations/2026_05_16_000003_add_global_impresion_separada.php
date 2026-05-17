<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('globales')->updateOrInsert(
            ['nom_var' => 'IMPRESION_SEPARADA'],
            [
                'valor_var' => '0',
                'descrip_var' => '0: imprime toda la comanda en un solo ticket. 1: imprime tickets separados por sector (Cocina y Barra).',
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('globales')->where('nom_var', 'IMPRESION_SEPARADA')->delete();
    }
};
