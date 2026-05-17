<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Mantener decimal(10, 1) para todos los precios - máximo 999999999.9
     */
    public function up(): void
    {
        // Los campos ya están como decimal(10, 1) desde la creación, no es necesario cambiar
        // Pero lo dejamos aquí como registro de que se handled correctamente
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No hay cambios que revertir
    }
};

