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
        Schema::create('borradores', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid_borrador')->comment('uuid del borrador');
            $table->foreignId('product_id')->constrained('productos')->comment('ID del producto');
            $table->string('producto', 255)->comment('Descripcion producto');
            $table->double('cantidad',15,1)->comment('Cantidad producto');
            $table->double('precio_Venta', 15, 0)->comment('Precio producto');
            $table->integer('descuento')->comment('Descuento producto');
            $table->datetime('fec_creacion')->comment('Fecha creación');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('borradores');
    }
};
