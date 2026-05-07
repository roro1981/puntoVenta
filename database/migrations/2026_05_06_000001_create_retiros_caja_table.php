<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retiros_caja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caja_id')->constrained('cajas', 'id')->comment('Caja activa desde la que se hace el retiro');
            $table->decimal('monto', 15, 2)->comment('Monto retirado de la caja');
            $table->string('motivo', 255)->comment('Motivo o descripción del retiro');
            $table->foreignId('creado_por')->constrained('users', 'id')->comment('Usuario que registró el retiro');
            $table->timestamp('created_at')->useCurrent()->comment('Fecha y hora del retiro');

            $table->index('caja_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retiros_caja');
    }
};
