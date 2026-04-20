<?php

use App\Models\OrderWoo;

class OrderEventHandler
{
    public function handle($data)
    {
        OrderWoo::updateOrCreate(
            ['woo_id' => $data['id']],
            [
                'email' => $data['billing']['email'] ?? null,
                'total' => $data['total'],
                'status' => $data['status'],
            ]
        );
    }
}