<?php

use App\Models\ProductWoo;

class ProductEventHandler
{
    public function handle($data)
    {
        ProductWoo::updateOrCreate(
            ['woo_id' => $data['id']],
            [
                'name' => $data['name'],
                'price' => $data['price'],
                'status' => $data['status']
            ]
        );
    }
}