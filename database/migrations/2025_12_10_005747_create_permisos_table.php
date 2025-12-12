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
        Schema::create('permisos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 100)->unique()->comment('Código único del permiso (ej: PERMISO_CIERRES_CAJA)');
            $table->string('nombre', 100)->comment('Nombre descriptivo del permiso');
            $table->string('descripcion', 255)->nullable()->comment('Descripción detallada del permiso');
            $table->string('modulo', 50)->nullable()->comment('Módulo al que pertenece (ej: Ventas, Productos, Reportes)');
            $table->boolean('activo')->default(true)->comment('Estado del permiso');
            $table->timestamps();
            
            // Índice para búsquedas
            $table->index('modulo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permisos');
    }
};
