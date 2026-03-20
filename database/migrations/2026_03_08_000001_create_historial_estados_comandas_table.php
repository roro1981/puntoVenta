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
        Schema::create('historial_estados_comandas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comanda_id')->constrained('comandas', 'id');
            $table->foreignId('mesa_id')->constrained('mesas', 'id');
            $table->string('estado_anterior', 50)->nullable();
            $table->string('estado_nuevo', 50);
            $table->string('accion', 50);
            $table->foreignId('usuario_id')->nullable()->constrained('users', 'id');
            $table->dateTime('fecha_cambio');
            $table->string('observacion', 500)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historial_estados_comandas');
    }
};
