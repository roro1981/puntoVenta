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
        Schema::create('detalles_ventas', function (Blueprint $table) {
            $table->id()->comment('ID de detalle de venta');
            $table->foreignId('venta_id')->constrained('ventas')->onDelete('restrict')->comment('Referencia a la venta');
            $table->uuid('producto_uuid')->nullable()->comment('UUID del producto');
            $table->string('descripcion_producto', 255)->comment('Descripción del producto/servicio');
            $table->decimal('cantidad', 10, 2)->comment('Cantidad vendida');
            $table->bigInteger('precio_unitario')->comment('Precio unitario sin descuento');
            $table->decimal('descuento_porcentaje', 5, 2)->default(0)->comment('Porcentaje de descuento aplicado');
            $table->bigInteger('subtotal_linea')->comment('Subtotal: (cantidad × precio_unitario) × (1 - descuento_porcentaje/100)');

            $table->index('venta_id');
            $table->index('producto_uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalles_ventas');
    }
};
