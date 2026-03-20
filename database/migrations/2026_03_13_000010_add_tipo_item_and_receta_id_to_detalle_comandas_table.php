<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('detalle_comandas', function (Blueprint $table) {
            if (!Schema::hasColumn('detalle_comandas', 'tipo_item')) {
                $table->enum('tipo_item', ['PRODUCTO', 'RECETA'])
                    ->default('PRODUCTO')
                    ->after('producto_id');
            }

            if (!Schema::hasColumn('detalle_comandas', 'receta_id')) {
                $table->unsignedBigInteger('receta_id')
                    ->nullable()
                    ->after('tipo_item');

                $table->foreign('receta_id')
                    ->references('id')
                    ->on('recetas')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detalle_comandas', function (Blueprint $table) {
            if (Schema::hasColumn('detalle_comandas', 'receta_id')) {
                $table->dropForeign(['receta_id']);
                $table->dropColumn('receta_id');
            }

            if (Schema::hasColumn('detalle_comandas', 'tipo_item')) {
                $table->dropColumn('tipo_item');
            }
        });
    }
};
