<?php


use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->comment('The name of the user');
            $table->string('name_complete', 255)->comment('The name complete of the user');
            $table->string('password', 255)->comment('The hashed password of the user');
            $table->foreignId('role_id')->constrained('roles','id')->comment('The role ID associated with the user');
            $table->integer('estado')->comment('State of the user');
            $table->timestamp('created_at')->comment('Fecha creación');
            $table->dateTime('updated_at')->nullable()->comment("Fecha actualizacion");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
