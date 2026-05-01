<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductWoo;

class ProductWooSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'woo_id' => 101,
                'name' => 'Kambing Qurban Tipe A',
                'price' => 2500000.00,
                'status' => 'publish',
            ],
            [
                'woo_id' => 102,
                'name' => 'Sapi Limousin Premium',
                'price' => 25000000.00,
                'status' => 'publish',
            ],
            [
                'woo_id' => 103,
                'name' => 'Domba Garut Super',
                'price' => 4000000.00,
                'status' => 'publish',
            ],
        ];

        foreach ($products as $product) {
            ProductWoo::updateOrCreate(['woo_id' => $product['woo_id']], $product);
        }
    }
}
