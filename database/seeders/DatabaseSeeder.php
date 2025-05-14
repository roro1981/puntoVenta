<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\FormaPago;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(MenusTableSeeder::class);
        $this->call(RolesTableSeeder::class);
        $this->call(SubmenusTableSeeder::class);
        $this->call(MenuRolesTableSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(CorporateDataTableSeeder::class);
        $this->call(ComunasTableSeeder::class);
        $this->call(GlobalesTableSeeder::class);
        $this->call(ImpuestosTableSeeder::class);
        $this->call(CategoriasTableSeeder::class);
        $this->call(InsumosSeeder::class);
        $this->call(RegionSeeder::class);
        $this->call(FormasPagoTableSeeder::class);
    }
}
