<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // --- ventas: consultas frecuentes por estado, fecha y usuario ---
        Schema::table('ventas', function (Blueprint $table) {
            $table->index('estado',       'idx_ventas_estado');
            $table->index('fecha_venta',  'idx_ventas_fecha_venta');
            $table->index('user_id',      'idx_ventas_user_id');
            // Índice compuesto para reportes de rango de fechas filtrados por estado
            $table->index(['fecha_venta', 'estado'], 'idx_ventas_fecha_estado');
        });

        // --- productos: búsquedas por uuid (cada venta/comanda lo usa) y filtros de lista ---
        Schema::table('productos', function (Blueprint $table) {
            $table->unique('uuid',                      'uq_productos_uuid');
            $table->index('estado',                     'idx_productos_estado');
            // Índice compuesto para listados filtrados por estado y categoría
            $table->index(['estado', 'categoria_id'],   'idx_productos_estado_cat');
        });

        // --- comandas: filtros por estado son el 90% del trabajo en restaurante ---
        Schema::table('comandas', function (Blueprint $table) {
            $table->index('estado', 'idx_comandas_estado');
        });

        // --- detalles_ventas: filtros por anulado en flujo de anulación ---
        Schema::table('detalles_ventas', function (Blueprint $table) {
            $table->index('anulado', 'idx_detalles_ventas_anulado');
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropIndex('idx_ventas_estado');
            $table->dropIndex('idx_ventas_fecha_venta');
            $table->dropIndex('idx_ventas_user_id');
            $table->dropIndex('idx_ventas_fecha_estado');
        });

        Schema::table('productos', function (Blueprint $table) {
            $table->dropUnique('uq_productos_uuid');
            $table->dropIndex('idx_productos_estado');
            $table->dropIndex('idx_productos_estado_cat');
        });

        Schema::table('comandas', function (Blueprint $table) {
            $table->dropIndex('idx_comandas_estado');
        });

        Schema::table('detalles_ventas', function (Blueprint $table) {
            $table->dropIndex('idx_detalles_ventas_anulado');
        });
    }
};
