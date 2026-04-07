<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detalles_ventas', function (Blueprint $table) {
            $table->foreignId('promo_id')
                ->nullable()
                ->after('producto_uuid')
                ->constrained('promociones', 'id')
                ->nullOnDelete()
                ->comment('FK a promociones — nulo si el ítem es un producto individual');
        });
    }

    public function down(): void
    {
        Schema::table('detalles_ventas', function (Blueprint $table) {
            $table->dropForeign(['promo_id']);
            $table->dropColumn('promo_id');
        });
    }
};
