<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Herramienta;

class HerramientaSeeder extends Seeder
{
    public function run(): void
    {
        Herramienta::insert([
            [
                'nombre' => 'Martillo',
                'descripcion' => 'Martillo de acero con mango de madera.',
                'precio' => 1500,
                'imagen' => 'https://cdn.pixabay.com/photo/2016/03/31/19/56/hammer-1294117_1280.png'
            ],
            [
                'nombre' => 'Destornillador',
                'descripcion' => 'Destornillador plano de acero inoxidable.',
                'precio' => 800,
                'imagen' => 'https://cdn.pixabay.com/photo/2014/12/21/23/41/screwdriver-575628_1280.png'
            ],
            [
                'nombre' => 'Llave Inglesa',
                'descripcion' => 'Ajustable, resistente y práctica.',
                'precio' => 2200,
                'imagen' => 'https://cdn.pixabay.com/photo/2016/03/31/20/10/wrench-1294125_1280.png'
            ],
        ]);
    }
}
