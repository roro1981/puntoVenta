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
        Schema::create('rangos_precios', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignId('producto_id')->constrained('productos', 'id')->comment('Producto asociado al rango de precio');
            $table->decimal('cantidad_minima', 10, 2)->comment('Cantidad mínima para aplicar este precio');
            $table->decimal('cantidad_maxima', 10, 2)->nullable()->comment('Cantidad maxima para aplicar este precio');
            $table->decimal('precio_unitario', 10, 2)->comment('Precio unitario aplicado dentro del rango');
            $table->datetime('fec_creacion')->comment('Fecha creación rango');
            $table->string('user_creacion', 100)->comment('Usuario que crea rango');
            $table->datetime('fec_modificacion')->nullable()->comment('Fecha modificacion rango');
            $table->string('user_modificacion', 100)->nullable()->comment('Usuario que modifica rango');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rangos_precios');
    }
};
