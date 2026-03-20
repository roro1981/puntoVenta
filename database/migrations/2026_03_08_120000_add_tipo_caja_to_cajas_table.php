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
        Schema::table('cajas', function (Blueprint $table) {
            $table->enum('tipo_caja', ['ALMACEN', 'RESTAURANT'])
                ->default('ALMACEN')
                ->after('user_id')
                ->comment('Tipo de negocio asociado a la apertura de caja');

            $table->index('tipo_caja');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cajas', function (Blueprint $table) {
            $table->dropIndex(['tipo_caja']);
            $table->dropColumn('tipo_caja');
        });
    }
};
