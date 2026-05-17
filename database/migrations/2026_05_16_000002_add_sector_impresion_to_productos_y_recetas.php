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
        Schema::table('productos', function (Blueprint $table) {
            $table->char('sector_impresion', 1)->default('C')->after('tipo');
        });

        Schema::table('recetas', function (Blueprint $table) {
            $table->char('sector_impresion', 1)->default('C')->after('descripcion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn('sector_impresion');
        });

        Schema::table('recetas', function (Blueprint $table) {
            $table->dropColumn('sector_impresion');
        });
    }
};
