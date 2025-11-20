<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventariosTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('inventarios')->insert([
            // Bodega Central
            [
                'id_bodega' => 1,
                'id_producto' => 1,
                'cantidad' => 10,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_bodega' => 1,
                'id_producto' => 2,
                'cantidad' => 25,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Bodega Norte
            [
                'id_bodega' => 2,
                'id_producto' => 3,
                'cantidad' => 15,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_bodega' => 2,
                'id_producto' => 4,
                'cantidad' => 8,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Bodega Sur
            [
                'id_bodega' => 3,
                'id_producto' => 1,
                'cantidad' => 5,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_bodega' => 3,
                'id_producto' => 3,
                'cantidad' => 12,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
