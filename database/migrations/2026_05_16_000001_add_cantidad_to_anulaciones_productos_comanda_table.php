<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('anulaciones_productos_comanda', function (Blueprint $table) {
            $table->integer('cantidad')->default(1)->after('motivo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('anulaciones_productos_comanda', function (Blueprint $table) {
            $table->dropColumn('cantidad');
        });
    }
};
