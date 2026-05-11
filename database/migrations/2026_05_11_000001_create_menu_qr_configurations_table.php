<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_qr_configurations', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_token')->unique();
            $table->json('selected_categories')->nullable();
            $table->json('selected_items')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_qr_configurations');
    }
};