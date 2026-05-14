<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_qr_configurations', function (Blueprint $table) {
            $table->string('nombre', 120)->nullable()->after('public_token');
        });

        DB::table('menu_qr_configurations')
            ->orderBy('id')
            ->get()
            ->each(function ($configuracion, $index) {
                DB::table('menu_qr_configurations')
                    ->where('id', $configuracion->id)
                    ->update([
                        'nombre' => $configuracion->nombre ?: 'Menu QR ' . ($index + 1),
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('menu_qr_configurations', function (Blueprint $table) {
            $table->dropColumn('nombre');
        });
    }
};