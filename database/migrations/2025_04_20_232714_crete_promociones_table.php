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

        Schema::create('promociones', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->string('codigo')->comment('Codigo promocion');
            $table->string('nombre')->comment('Nombre promocion');
            $table->foreignId('categoria_id')->constrained('categorias', 'id')->comment('Id de categoria asociada a receta');
            $table->bigInteger('precio_costo')->comment('Costo receta');
            $table->bigInteger('precio_venta')->comment('Precio publico receta');
            $table->date('fecha_inicio')->nullable()->comment('Fecha Inicio promocion');
            $table->date('fecha_fin')->nullable()->comment('Fecha Fin promocion');
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
        Schema::dropIfExists('promociones');
    }
};
