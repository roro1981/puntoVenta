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
            $table->dropForeign(['producto_id']);
            $table->unsignedBigInteger('producto_id')->nullable()->change();
            $table->foreign('producto_id')
                ->references('id')
                ->on('productos')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detalle_comandas', function (Blueprint $table) {
            $table->dropForeign(['producto_id']);
            $table->unsignedBigInteger('producto_id')->nullable(false)->change();
            $table->foreign('producto_id')
                ->references('id')
                ->on('productos');
        });
    }
};
