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
        // Insertar la fila mail_enterprise si no existe aún
        if (!\DB::table('corporate_data')->where('item', 'mail_enterprise')->exists()) {
            \DB::table('corporate_data')->insert([
                'item'             => 'mail_enterprise',
                'description_item' => null,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \DB::table('corporate_data')->where('item', 'mail_enterprise')->delete();
    }
};
