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
        Schema::create('movimientos_prod', function (Blueprint $table) {
            $table->id(); 
            $table->foreignId('producto_id')->constrained('productos', 'id')->comment('Producto');
            $table->integer('cantidad');
            $table->string('tipo_movi', 1);
            $table->string('obs', 500);
            $table->dateTime('fec_mov');
            $table->string('usuario_mov', 20);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos_prod');
    }
};
