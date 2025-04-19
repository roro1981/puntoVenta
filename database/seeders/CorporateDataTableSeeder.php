<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CorporateDataTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('corporate_data')->insert([
            ['item' => 'name_enterprise'],
            ['item' => 'fantasy_name_enterprise'],
            ['item' => 'address_enterprise'],
            ['item' => 'comuna_enterprise'],
            ['item' => 'phone_enterprise'],
            ['item' => 'logo_enterprise', 'description_item' => '/img/fotos_prod/sin_imagen.jpg'],
        ]);
    }
}
