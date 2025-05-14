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
        Schema::create('facturas', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->unsignedBigInteger('num_factura')->unique();
            $table->foreignId('prov_id')->constrained('proveedores', 'id')->comment('Datos proveedor');
            $table->double('neto', 15, 2);
            $table->double('impuestos', 15, 2);
            $table->string('desglose_impuestos', 300);
            $table->double('total_fact', 15, 2);
            $table->date('fecha_doc');
            $table->integer('dias');
            $table->string('fpago', 100);
            $table->date('vencimiento');
            $table->string('estado', 10);
            $table->string('foto', 2000)->default('');
            $table->string('usuario_foto', 200)->default('');
            $table->datetime('fec_creacion')->comment('Fecha creación factura');
            $table->string('user_creacion', 100)->comment('Usuario que crea factura');
            $table->datetime('fec_modificacion')->nullable()->comment('Fecha modificacion factura');
            $table->string('user_modificacion', 100)->nullable()->comment('Usuario que modifica factura');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facturas');
    }
};
