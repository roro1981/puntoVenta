<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('categorias', function (Blueprint $table) {
            $table->dateTime('fec_creacion')->nullable()->after('estado_categoria')->comment('Fecha creación categoria');
            $table->string('user_creacion', 255)->nullable()->after('fec_creacion')->comment('Usuario creación categoria');
            $table->dateTime('fec_eliminacion')->nullable()->after('user_creacion')->comment('Fecha eliminación categoria');
            $table->string('user_eliminacion', 255)->nullable()->after('fec_eliminacion')->comment('Usuario eliminación categoria');
        });

        DB::table('categorias')
            ->whereNull('fec_creacion')
            ->update([
                'fec_creacion' => now(),
                'user_creacion' => 'SISTEMA',
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categorias', function (Blueprint $table) {
            $table->dropColumn([
                'fec_creacion',
                'user_creacion',
                'fec_eliminacion',
                'user_eliminacion',
            ]);
        });
    }
};
