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
        Schema::create('cajas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users', 'id')->comment('Usuario que opera la caja');
            $table->dateTime('fecha_apertura')->comment('Fecha y hora de apertura de caja');
            $table->dateTime('fecha_cierre')->nullable()->comment('Fecha y hora de cierre de caja');
            $table->decimal('monto_inicial', 15, 2)->default(0)->comment('Monto inicial con el que se abre la caja');
            $table->decimal('monto_ventas', 15, 2)->default(0)->comment('Total de ventas realizadas durante el turno');
            $table->decimal('monto_efectivo', 15, 2)->default(0)->comment('Total en efectivo durante el turno');
            $table->decimal('monto_tarjeta', 15, 2)->default(0)->comment('Total en tarjetas durante el turno');
            $table->decimal('monto_transferencia', 15, 2)->default(0)->comment('Total en transferencias durante el turno');
            $table->decimal('monto_otros', 15, 2)->default(0)->comment('Total otros medios de pago durante el turno');
            $table->decimal('monto_final_declarado', 15, 2)->nullable()->comment('Monto final declarado al cerrar caja');
            $table->decimal('diferencia', 15, 2)->nullable()->comment('Diferencia entre lo esperado y lo declarado');
            $table->enum('estado', ['abierta', 'cerrada'])->default('abierta')->comment('Estado actual de la caja');
            $table->text('observaciones')->nullable()->comment('Observaciones o notas sobre el turno de caja');
            
            // Índices para mejorar búsquedas
            $table->index('user_id');
            $table->index('estado');
            $table->index('fecha_apertura');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cajas');
    }
};
