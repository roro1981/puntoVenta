<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Realinea comandas.garzon_id para que apunte a users.id.
     */
    public function up(): void
    {
        if (!Schema::hasTable('comandas') || !Schema::hasTable('users') || !Schema::hasColumn('comandas', 'garzon_id')) {
            return;
        }

        Schema::table('comandas', function (Blueprint $table) {
            try {
                $table->dropForeign(['garzon_id']);
            } catch (\Throwable $e) {
                // La FK puede no existir en algunos entornos.
            }
        });

        DB::table('comandas')
            ->whereNotNull('garzon_id')
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('users')
                    ->whereColumn('users.id', 'comandas.garzon_id');
            })
            ->update(['garzon_id' => null]);

        Schema::table('comandas', function (Blueprint $table) {
            try {
                $table->foreign('garzon_id')->references('id')->on('users')->nullOnDelete();
            } catch (\Throwable $e) {
                // Ya existe una FK compatible.
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('comandas') || !Schema::hasColumn('comandas', 'garzon_id')) {
            return;
        }

        Schema::table('comandas', function (Blueprint $table) {
            try {
                $table->dropForeign(['garzon_id']);
            } catch (\Throwable $e) {
                // Sin acción en rollback si no existe.
            }
        });
    }
};
