<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Globales;
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

        $tipoNegocio = (string) Globales::whereRaw('LOWER(nom_var) = ?', ['tipo_negocio'])
            ->value('valor_var');

        if (mb_strtoupper(trim($tipoNegocio), 'UTF-8') === 'RESTAURANT') {
            $this->call(InsumosSeeder::class);
        }

        $this->call(RegionSeeder::class);
        $this->call(FormasPagoTableSeeder::class);
        $this->call(ProductosTableSeeder::class);
        $this->call(PermisosRolesSeeder::class);
        $this->call(MesasTableSeeder::class);
        $this->call(GarzonesSeeder::class);
    }
}
