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
        Schema::create('detalle_factura', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->unsignedBigInteger('num_factura');
            $table->foreign('num_factura')->references('num_factura')->on('facturas')->comment('Numero factura');
            $table->string('cod_producto', 100);
            $table->float('cantidad');
            $table->double('precio', 15, 2);
            $table->float('descuento');
            $table->float('imp1');
            $table->float('imp2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_factura');
    }
};
