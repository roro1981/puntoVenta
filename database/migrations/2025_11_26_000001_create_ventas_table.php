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
        Schema::create('ventas', function (Blueprint $table) {
            $table->id()->comment('ID de venta');
            $table->string('numero_venta', 50)->unique()->nullable()->comment('Número de venta único');
            $table->bigInteger('total')->default(0)->comment('Total de la venta');
            $table->bigInteger('total_descuentos')->default(0)->comment('Total de descuentos aplicados');
            $table->foreignId('user_id')->constrained('users')->comment('Usuario que realizó la venta');
            $table->string('forma_pago', 100)->nullable()->comment('Forma de pago utilizada');
            $table->string('estado', 50)->default('completada')->comment('Estado: completada, anulada, pendiente');
            $table->dateTime('fecha_venta')->useCurrent()->comment('Fecha y hora de la venta');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
