<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_estados_ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->constrained('ventas', 'id');
            $table->string('estado_anterior', 50)->nullable();
            $table->string('estado_nuevo', 50);
            $table->string('accion', 100);
            $table->foreignId('usuario_id')->nullable()->constrained('users', 'id');
            $table->dateTime('fecha_cambio');
            $table->string('observacion', 500)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_estados_ventas');
    }
};
