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
        Schema::create('promociones_detalle', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignId('promo_id')->constrained('promociones', 'id')->comment('Id de promocion asociada');
            $table->foreignId('producto_id')->constrained('productos', 'id')->comment('Id de producto asociado a detalle');
            $table->decimal('cantidad', 8, 2)->comment('Cantidad de producto');
            $table->string('unidad')->nullable()->comment('Unidad de medida de producto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promociones_detalle');
    }
};
