<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mesas', function (Blueprint $table) {
            $table->boolean('reservada')->default(false)->after('activa');
            $table->unsignedBigInteger('reservada_por_user_id')->nullable()->after('reservada');
            $table->timestamp('reservada_at')->nullable()->after('reservada_por_user_id');

            $table->foreign('reservada_por_user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('mesas', function (Blueprint $table) {
            $table->dropForeign(['reservada_por_user_id']);
            $table->dropColumn(['reservada', 'reservada_por_user_id', 'reservada_at']);
        });
    }
};