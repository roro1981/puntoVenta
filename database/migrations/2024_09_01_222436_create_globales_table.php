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
        Schema::create('globales', function (Blueprint $table) {
            $table->id();
            $table->string('nom_var', 255)->comment('nombre de la variable global');;
            $table->string('valor_var', 255)->comment('valor de la variable global');;
            $table->string('descrip_var', 255)->comment('descripcion de la variable global');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('globales');
    }
};
