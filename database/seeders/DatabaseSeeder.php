<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            UsersTableSeeder::class,
            BodegasTableSeeder::class,
            ProductosTableSeeder::class,
            InventariosTableSeeder::class,
        ]);
    }
}
