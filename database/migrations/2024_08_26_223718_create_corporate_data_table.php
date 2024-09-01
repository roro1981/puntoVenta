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
        Schema::create('corporate_data', function (Blueprint $table) {
            $table->id();
            $table->string('item', 255)->comment('Name item');
            $table->string('description_item', 255)->nullable()->comment('Description item');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('corporate_data');
    }
};
