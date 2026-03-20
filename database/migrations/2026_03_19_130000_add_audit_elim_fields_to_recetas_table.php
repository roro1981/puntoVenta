<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recetas', function (Blueprint $table) {
            $table->datetime('fec_eliminacion')->nullable()->after('user_modificacion');
            $table->string('user_eliminacion')->nullable()->after('fec_eliminacion');
        });
    }

    public function down(): void
    {
        Schema::table('recetas', function (Blueprint $table) {
            $table->dropColumn(['fec_eliminacion', 'user_eliminacion']);
        });
    }
};
