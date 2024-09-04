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
        Schema::create('impuestos', function (Blueprint $table) {
            $table->id();
            $table->string('nom_imp', 255)->comment('nombre del impuesto');;
            $table->decimal('valor_imp', 3,1)->comment('valor del impuesto');;
            $table->text('descrip_imp')->comment('descripcion del impuesto');
            $table->datetime('last_activity')->comment("fecha ultima modificacion de impuesto");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('impuestos');
    }
};
