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
        Schema::table('detalles_ventas', function (Blueprint $table) {
            $table->boolean('anulado')->default(false)->after('subtotal_linea')->comment('Si el detalle está anulado');
            $table->dateTime('fecha_anulacion')->nullable()->after('anulado')->comment('Fecha y hora de anulación');
            $table->foreignId('user_anulacion_id')->nullable()->after('fecha_anulacion')->constrained('users')->comment('Usuario que anuló');
            $table->text('motivo_anulacion')->nullable()->after('user_anulacion_id')->comment('Motivo de la anulación');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detalles_ventas', function (Blueprint $table) {
            $table->dropForeign(['user_anulacion_id']);
            $table->dropColumn(['anulado', 'fecha_anulacion', 'user_anulacion_id', 'motivo_anulacion']);
        });
    }
};
