<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            ['name' => 'roro1981', 'name_complete' => 'Rodrigo Panes', 'password' => Hash::make('panes1981'), 'role_id' => 3, 'estado' => 1, 'created_at' => Carbon::now()->toDateTimeString()],
        ]);
    }
}
