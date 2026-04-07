<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_precios', function (Blueprint $table) {
            $table->id();
            // entidad_tipo: PRODUCTO | RECETA | PROMOCION
            $table->string('entidad_tipo', 20);
            $table->unsignedBigInteger('entidad_id');
            // campo que cambió: precio_venta | precio_compra_neto
            $table->string('campo', 30);
            $table->decimal('precio_anterior', 12, 2)->nullable()->comment('null en el registro inicial de creación');
            $table->decimal('precio_nuevo', 12, 2);
            $table->string('usuario', 100);
            $table->dateTime('fecha_cambio');

            $table->index(['entidad_tipo', 'entidad_id']);
            $table->index('fecha_cambio');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_precios');
    }
};
