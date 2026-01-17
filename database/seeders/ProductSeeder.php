<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            ['name' => 'Mineral 600 ml', 'price' => 3000, 'stock' => 50],
            ['name' => 'Mineral 1500 ml', 'price' => 5000, 'stock' => 20],
            ['name' => 'Hydro coco', 'price' => 8000, 'stock' => 15],
            ['name' => 'Susu Jelly', 'price' => 10000, 'stock' => 10],
            ['name' => 'WHEY', 'price' => 12000, 'stock' => 25],
            ['name' => 'Pocari', 'price' => 8000, 'stock' => 20],
            ['name' => 'Denda Kartu', 'price' => 10000, 'stock' => 999],
        ];

        foreach ($products as $prod) {
            \App\Models\Product::updateOrCreate(
                ['name' => $prod['name']],
                $prod
            );
        }
    }
}
