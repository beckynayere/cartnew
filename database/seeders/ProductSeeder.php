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
        //
            Product::create([
                'name' => 'Sample Product 1',
                'description' => 'A sample product description.',
                'price' => 100.00,
                'stock' => 50,
            ]);
            
            Product::create([
                'name' => 'Sample Product 2',
                'description' => 'Another sample product description.',
                'price' => 200.00,
                'stock' => 30,
            ]);
    }
}
