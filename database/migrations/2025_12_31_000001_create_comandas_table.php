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
        Schema::create('comandas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mesa_id');
            $table->unsignedBigInteger('user_id'); // Mesero que atiende
            $table->string('numero_comanda', 20)->unique();
            $table->enum('estado', ['ABIERTA', 'CERRADA', 'CANCELADA'])->default('ABIERTA');
            $table->decimal('total', 10, 2)->default(0);
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('impuestos', 10, 2)->default(0);
            $table->datetime('fecha_apertura');
            $table->datetime('fecha_cierre')->nullable();
            $table->text('observaciones')->nullable();
            $table->datetime('created_at')->nullable();
            $table->datetime('updated_at')->nullable();

            $table->foreign('mesa_id')->references('id')->on('mesas');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comandas');
    }
};
