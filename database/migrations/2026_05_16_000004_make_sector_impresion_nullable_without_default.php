<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE productos MODIFY sector_impresion CHAR(1) NULL DEFAULT NULL");
        DB::statement("ALTER TABLE recetas MODIFY sector_impresion CHAR(1) NULL DEFAULT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("UPDATE productos SET sector_impresion = 'C' WHERE sector_impresion IS NULL");
        DB::statement("UPDATE recetas SET sector_impresion = 'C' WHERE sector_impresion IS NULL");
        DB::statement("ALTER TABLE productos MODIFY sector_impresion CHAR(1) NOT NULL DEFAULT 'C'");
        DB::statement("ALTER TABLE recetas MODIFY sector_impresion CHAR(1) NOT NULL DEFAULT 'C'");
    }
};
