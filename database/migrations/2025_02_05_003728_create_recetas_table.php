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
        Schema::create('recetas', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->string('codigo')->comment('Codigo receta');
            $table->string('nombre')->comment('Nombre receta');
            $table->text('descripcion')->nullable()->comment('Descripción más detallada o instrucciones de preparación');
            $table->bigInteger('precio_costo')->comment('Costo receta');
            $table->bigInteger('precio_venta')->comment('Precio publico receta');
            $table->string('imagen', 255)->nullable()->comment('Imagen receta');
            $table->string('estado', 10)->comment('Estado receta: Activo | Inactivo');
            $table->datetime('fec_creacion')->comment('Fecha creación receta');
            $table->string('user_creacion', 100)->comment('Usuario que crea receta');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recetas');
    }
};
