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
        Schema::table('comandas', function (Blueprint $table) {
            $table->foreignId('garzon_id')->nullable()->after('user_id')->comment('ID del garzón asignado');
            $table->foreign('garzon_id')->references('id')->on('garzones');
            $table->decimal('propina', 10, 0)->default(0)->after('total')->comment('Propina 10%');
            $table->boolean('incluye_propina')->default(false)->after('propina')->comment('Si incluye propina');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comandas', function (Blueprint $table) {
            $table->dropForeign(['garzon_id']);
            $table->dropColumn(['garzon_id', 'propina', 'incluye_propina']);
        });
    }
};
