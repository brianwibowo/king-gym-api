<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Package;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class KingGymSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Akun Owner (Supaya bisa login nanti)
        User::create([
            'name' => 'Owner King Gym',
            'email' => 'owner@kinggym.com',
            'password' => Hash::make('password123'),
        ]);

        // 2. Data Membership
        Package::insert([
            ['name' => 'Harian Mahasiswa', 'price' => 12000, 'duration_days' => 1, 'category' => 'mahasiswa'],
            ['name' => 'Harian Umum', 'price' => 15000, 'duration_days' => 1, 'category' => 'umum'],
            ['name' => 'Member Mahasiswa', 'price' => 120000, 'duration_days' => 30, 'category' => 'mahasiswa'],
            ['name' => 'Member Umum', 'price' => 150000, 'duration_days' => 30, 'category' => 'umum'],
            ['name' => 'Member Mahasiswa 3 Bulan', 'price' => 300000, 'duration_days' => 90, 'category' => 'mahasiswa'],
            ['name' => 'Member Umum 3 Bulan', 'price' => 400000, 'duration_days' => 90, 'category' => 'umum'],
            ['name' => 'Member Couple Mahasiswa', 'price' => 220000, 'duration_days' => 30, 'category' => 'couple'],
            ['name' => 'Member Couple Umum', 'price' => 280000, 'duration_days' => 30, 'category' => 'couple'],
        ]);

        // 3. Data Produk FnB
        Product::insert([
            ['name' => 'Mineral 600 ml', 'price' => 3000, 'stock' => 50],
            ['name' => 'Mineral 1500 ml', 'price' => 5000, 'stock' => 20],
            ['name' => 'Hydro coco', 'price' => 8000, 'stock' => 15],
            ['name' => 'Susu Jelly', 'price' => 10000, 'stock' => 10],
            ['name' => 'WHEY', 'price' => 12000, 'stock' => 25],
            ['name' => 'Pocari', 'price' => 8000, 'stock' => 20],
            ['name' => 'Denda Kartu', 'price' => 10000, 'stock' => 999],
        ]);
    }
}