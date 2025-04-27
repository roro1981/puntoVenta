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
            ['item' => 'name_enterprise', 'description_item' => null],
            ['item' => 'fantasy_name_enterprise', 'description_item' => null],
            ['item' => 'address_enterprise', 'description_item' => null],
            ['item' => 'comuna_enterprise', 'description_item' => null],
            ['item' => 'phone_enterprise', 'description_item' => null],
            ['item' => 'logo_enterprise', 'description_item' => '/img/fotos_prod/sin_imagen.jpg'],
        ]);
    }
}
