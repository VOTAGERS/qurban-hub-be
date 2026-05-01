<?php

namespace App\Services\Woo;

use App\Models\OrderItemWoo;
use App\Models\OrderWoo;

class OrderEventHandler
{
    public function handle($data)
    {
        $order = OrderWoo::updateOrCreate(
            ['woo_id' => $data['id']],
            [
                'email' => $data['billing']['email'] ?? null,
                'total' => $data['total'],
                'status' => $data['status'],
                'raw_payload' => $data
            ]
        );

        // sync items
        foreach ($data['line_items'] as $item) {
            OrderItemWoo::updateOrCreate(
                [
                    'order_id' => $order->id,
                    'product_name' => $item['name']
                ],
                [
                    'qty' => $item['quantity'],
                    'price' => $item['price']
                ]
            );
        }
    }
}