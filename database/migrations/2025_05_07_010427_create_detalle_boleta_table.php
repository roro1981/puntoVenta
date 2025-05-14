<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_boleta', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->unsignedBigInteger('num_boleta');
            $table->foreign('num_boleta')->references('num_boleta')->on('boletas')->comment('Numero boleta');
            $table->string('cod_prod', 100);
            $table->float('cantidad');
            $table->double('precio', 15, 8);
            $table->float('descu');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_boleta');
    }
};
