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
        Schema::create('historial_movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos', 'id')->comment('Producto');
            $table->double('cantidad', 15, 1);
            $table->double('stock', 15, 1)->nullable();
            $table->string('tipo_mov', 50);
            $table->dateTime('fecha');
            $table->string('num_doc', 45);
            $table->string('obs', 500);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historial_movimientos');
    }
};
