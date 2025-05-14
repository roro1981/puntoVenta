<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos_factura', function (Blueprint $table) {
            $table->id('idpago');
            $table->foreignId('nro_factura')->constrained('facturas', 'num_factura')->comment('Número de factura pagada');
            $table->double('monto_pago')->default(0)->comment('Monto del pago realizado');
            $table->string('fpago', 100)->nullable()->comment('Forma de pago');
            $table->string('num_docu', 100)->nullable()->comment('Número de documento de pago');
            $table->dateTime('fecha_pago')->comment('Fecha del pago');
            $table->string('usuario', 100)->nullable()->comment('Usuario que registra el pago');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos_factura');
    }
};
