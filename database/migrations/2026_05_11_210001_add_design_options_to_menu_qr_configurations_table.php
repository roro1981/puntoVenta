<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_qr_configurations', function (Blueprint $table) {
            $table->json('design_options')->nullable()->after('design_tokens');
        });
    }

    public function down(): void
    {
        Schema::table('menu_qr_configurations', function (Blueprint $table) {
            $table->dropColumn('design_options');
        });
    }
};
