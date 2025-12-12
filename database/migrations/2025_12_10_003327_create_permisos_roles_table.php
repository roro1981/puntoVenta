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
        Schema::create('permisos_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles', 'id')->onDelete('cascade')->comment('ID del rol');
            $table->string('codigo_permiso', 100)->comment('Código único del permiso (ej: PERMISO_CIERRES_CAJA)');
            $table->string('descripcion', 255)->nullable()->comment('Descripción del permiso');
            $table->boolean('activo')->default(true)->comment('Estado del permiso');
            $table->timestamps();
            
            // Índice único para evitar duplicados
            $table->unique(['role_id', 'codigo_permiso'], 'unique_role_permiso');
            
            // Índice para búsquedas por código
            $table->index('codigo_permiso');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permisos_roles');
    }
};
