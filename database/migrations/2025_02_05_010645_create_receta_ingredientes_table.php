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
        Schema::create('receta_ingredientes', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignId('receta_id')->constrained('recetas', 'id')->comment('Id de receta asociada a ingrediente');
            $table->foreignId('producto_id')->constrained('productos', 'id')->comment('Id de producto asociado a ingrediente');
            $table->decimal('cantidad', 8, 2)->comment('Cantidad de producto');
            $table->string('unidad')->nullable()->comment('Unidad de medida de insumo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receta_ingredientes');
    }
};
