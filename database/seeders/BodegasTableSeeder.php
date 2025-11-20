<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BodegasTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('bodegas')->insert([
            [
                'nombre' => 'Bodega Central',
                'id_responsable' => 2,
                'estado' => true,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Bodega Norte',
                'id_responsable' => 3,
                'estado' => true,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Bodega Sur',
                'id_responsable' => 2,
                'estado' => true,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
