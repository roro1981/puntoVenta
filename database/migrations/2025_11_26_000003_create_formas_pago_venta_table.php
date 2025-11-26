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
        Schema::create('formas_pago_venta', function (Blueprint $table) {
            $table->id()->comment('ID de forma de pago de venta');
            $table->foreignId('venta_id')->constrained('ventas')->comment('Referencia a la venta');
            $table->string('forma_pago', 50)->comment('Forma de pago: EFECTIVO, TARJETA_DEBITO, etc.');
            $table->bigInteger('monto')->comment('Monto pagado con esta forma de pago');
            $table->timestamp('created_at')->useCurrent()->comment('Fecha y hora de registro');

            $table->index('venta_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('formas_pago_venta');
    }
};
