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
        Schema::create('mesas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50)->comment('Nombre de la mesa');
            $table->integer('orden')->default(0)->comment('Orden de visualización');
            $table->integer('capacidad')->default(4)->comment('Capacidad de personas');
            $table->boolean('activa')->default(true)->comment('Estado de la mesa');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mesas');
    }
};
