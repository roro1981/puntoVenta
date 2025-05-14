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
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->string('codigo', 100)->comment('Codigo producto');
            $table->string('descripcion', 255)->comment('Descripcion producto');
            $table->decimal('precio_compra_neto', 10, 1)->comment('Precio compra neto');
            $table->bigInteger('precio_compra_bruto')->comment('Precio compra bruto');
            $table->bigInteger('precio_venta')->comment('Precio venta publico');
            $table->decimal('stock', 6, 1)->default(0.0)->comment('Stock producto');
            $table->decimal('stock_minimo', 6, 1)->comment('Stock minimo producto');
            $table->foreignId('categoria_id')->constrained('categorias', 'id')->comment('Id de categoria asociada a producto');
            $table->enum('tipo', ['P', 'S', 'I', 'PR', 'R'])->comment('Tipo de producto');
            //$table->decimal('impuesto1', 3, 1)->comment('Primer impuesto');
            $table->foreignId('impuesto1')->constrained('impuestos', 'id')->comment('Id de primer impuesto');
            //$table->decimal('impuesto2', 3, 1)->nullable()->comment('Segundo impuesto');
            $table->foreignId('impuesto2')->nullable()->constrained('impuestos', 'id')->comment('Id de segundo impuesto');
            $table->string('imagen', 255)->nullable()->comment('Imagen producto');
            $table->string('unidad_medida', 5)->comment('Unidad de medida de producto');
            $table->string('estado', 10)->comment('Estado producto: Activo | Inactivo');
            $table->datetime('fec_creacion')->comment('Fecha creación');
            $table->string('user_creacion', 100)->comment('Usuario que crea producto');
            $table->datetime('fec_modificacion')->nullable()->comment('Fecha modificacion');
            $table->string('user_modificacion', 100)->nullable()->comment('Usuario que modificó producto');
            $table->datetime('fec_eliminacion')->nullable()->comment('Fecha eliminacion');
            $table->string('user_eliminacion', 100)->nullable()->comment('Usuario que eliminó producto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
