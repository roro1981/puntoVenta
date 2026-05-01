<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('globales')->insertOrIgnore([
            [
                'nom_var'    => 'SISTEMA_ACTIVO',
                'valor_var'  => '1',
                'descrip_var' => 'Activa o desactiva la generacion de nuevas ventas, preventas y comandas. 1: Activo, 0: Suspendido (falta de pago)',
            ]
        ]);
    }

    public function down(): void
    {
        DB::table('globales')->where('nom_var', 'SISTEMA_ACTIVO')->delete();
    }
};
