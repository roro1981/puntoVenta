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
            $table->foreignId('categoria_id')->constrained('categorias', 'id')->comment('Id de categoria asociada a receta');
            $table->decimal('precio_costo', 8, 1)->comment('Costo receta');
            $table->bigInteger('precio_venta')->comment('Precio publico receta');
            $table->string('imagen', 255)->nullable()->comment('Imagen receta');
            $table->string('estado', 10)->comment('Estado receta: Activo | Inactivo');
            $table->datetime('fec_creacion')->comment('Fecha creación receta');
            $table->string('user_creacion', 100)->comment('Usuario que crea receta');
            $table->datetime('fec_modificacion')->nullable()->comment('Fecha modificacion receta');
            $table->string('user_modificacion', 100)->nullable()->comment('Usuario que modifica receta');
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
