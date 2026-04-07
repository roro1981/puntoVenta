<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Agregar columna nueva como nullable primero
        Schema::table('detalle_factura', function (Blueprint $table) {
            $table->unsignedBigInteger('id_prod')->nullable()->after('uuid');
        });

        // 2. Rellenar con el id del producto buscando por código
        DB::statement('
            UPDATE detalle_factura df
            INNER JOIN productos p ON p.codigo = df.cod_producto
            SET df.id_prod = p.id
        ');

        // 3. Hacer NOT NULL y agregar FK
        Schema::table('detalle_factura', function (Blueprint $table) {
            $table->unsignedBigInteger('id_prod')->nullable(false)->change();
            $table->foreign('id_prod')->references('id')->on('productos');
        });

        // 4. Eliminar columna antigua
        Schema::table('detalle_factura', function (Blueprint $table) {
            $table->dropColumn('cod_producto');
        });
    }

    public function down(): void
    {
        Schema::table('detalle_factura', function (Blueprint $table) {
            $table->dropForeign(['id_prod']);
            $table->dropColumn('id_prod');
            $table->string('cod_producto', 100)->nullable()->after('uuid');
        });
    }
};
