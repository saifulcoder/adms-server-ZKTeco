<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OficinasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $oficinas = [
            [
                'idempresa' => 2,
                'idoficina' => 4,
                'ubicacion' => 'CDMX',
                'iatacode' => 'MEX',
                'public_url' => 'https://unitedmex.mindware.com.mx',
            ],
            [
                'idempresa' => 2,
                'idoficina' => 1,
                'ubicacion' => 'Cancun',
                'iatacode' => 'CUN',
                'public_url' => 'https://unitedcun.mindware.com.mx',
            ],
            [
                'idempresa' => 2,
                'idoficina' => 5,
                'ubicacion' => 'Puerto Vallarta',
                'iatacode' => 'PVR',
                'public_url' => 'https://unitedpvr.mindware.com.mx',
            ],
        ];

        foreach ($oficinas as $oficina) {
            \App\Models\Oficina::create($oficina);
        }
    }
}
