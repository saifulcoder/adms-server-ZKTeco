<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JadwalSholatSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'nama' => 'Subuh',
                'dari' => '03:30:00',
                'sampai' => '04:45:00',
                'terlambat' => false,
            ],
            [
                'nama' => 'Dhuhur',
                'dari' => '09:00:00',
                'sampai' => '12:15:00',
                'terlambat' => false,
            ],
            [
                'nama' => 'Ashar',
                'dari' => '14:30:00',
                'sampai' => '15:15:00',
                'terlambat' => false,
            ],
            [
                'nama' => 'Magrib',
                'dari' => '17:30:00',
                'sampai' => '18:15:00',
                'terlambat' => false,
            ],
            [
                'nama' => 'Isya',
                'dari' => '18:30:00',
                'sampai' => '23:00:00',
                'terlambat' => false,
            ],
        ];

        DB::table('jadwal_sholat')->insert($data);
    }
}

