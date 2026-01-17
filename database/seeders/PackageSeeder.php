<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $packages = [
            ['name' => 'Harian Mahasiswa', 'price' => 12000, 'duration_days' => 1, 'category' => 'mahasiswa'],
            ['name' => 'Harian Umum', 'price' => 15000, 'duration_days' => 1, 'category' => 'umum'],
            ['name' => 'Member Mahasiswa', 'price' => 120000, 'duration_days' => 30, 'category' => 'mahasiswa'],
            ['name' => 'Member Umum', 'price' => 150000, 'duration_days' => 30, 'category' => 'umum'],
            ['name' => 'Member Mahasiswa 3 Bulan', 'price' => 300000, 'duration_days' => 90, 'category' => 'mahasiswa'],
            ['name' => 'Member Umum 3 Bulan', 'price' => 400000, 'duration_days' => 90, 'category' => 'umum'],
            ['name' => 'Member Couple Mahasiswa', 'price' => 220000, 'duration_days' => 30, 'category' => 'couple'],
            ['name' => 'Member Couple Umum', 'price' => 280000, 'duration_days' => 30, 'category' => 'couple'],
        ];

        foreach ($packages as $pkg) {
            \App\Models\Package::updateOrCreate(
                ['name' => $pkg['name']],
                $pkg
            );
        }
    }
}
