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
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->string('rut', 12)->unique()->comment('RUT del proveedor');
            $table->string('razon_social', 255)->comment('Razón social (nombre legal)');
            $table->string('nombre_fantasia', 255)->nullable()->comment('Nombre de fantasía (opcional)');
            $table->string('giro', 255)->nullable()->comment('Actividad económica o giro SII');
            $table->string('direccion', 255)->nullable()->comment('Dirección fiscal o de despacho');
            $table->foreignId('region_id')->constrained('regiones', 'id')->comment('Region proveedor');
            $table->foreignId('comuna_id')->constrained('comunas', 'id')->comment('Comuna proveedor');
            $table->string('telefono', 20)->nullable()->comment('Teléfono principal');
            $table->string('email', 150)->nullable()->comment('Correo electrónico principal');
            $table->string('pagina_web', 150)->nullable()->comment('Sitio web');
            $table->string('contacto_nombre', 150)->nullable()->comment('Nombre del contacto comercial');
            $table->string('contacto_email', 150)->nullable()->comment('Email del contacto comercial');
            $table->string('contacto_telefono', 20)->nullable()->comment('Teléfono del contacto comercial');
            $table->string('estado')->comment('Estado proveedor');
            $table->datetime('fec_creacion')->comment('Fecha creación proveedor');
            $table->string('user_creacion', 100)->comment('Usuario que crea proveedor');
            $table->datetime('fec_modificacion')->nullable()->comment('Fecha modificacion proveedor');
            $table->string('user_modificacion', 100)->nullable()->comment('Usuario que modifica proveedor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proveedores');
    }
};
