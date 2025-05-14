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
        Schema::create('boletas', function (Blueprint $table) {
            $table->id(); 
            $table->uuid('uuid');
            $table->unsignedBigInteger('num_boleta')->unique();
            $table->foreignId('prov_id')->constrained('proveedores', 'id')->comment('Datos proveedor');
            $table->integer('tot_boleta');
            $table->date('fecha_boleta');
            $table->string('foto', 2000)->default('');
            $table->string('usuario_foto', 200)->default('');
            $table->datetime('fec_creacion')->comment('Fecha creación boleta');
            $table->string('user_creacion', 100)->comment('Usuario que crea boleta');
            $table->datetime('fec_modificacion')->nullable()->comment('Fecha modificacion boleta');
            $table->string('user_modificacion', 100)->nullable()->comment('Usuario que modifica boleta');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boletas');
    }
};
