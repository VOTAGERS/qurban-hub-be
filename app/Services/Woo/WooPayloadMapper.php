<?php 

class WooPayloadMapper {
    public static function mapOrder($event, $data){
        return [
            'type' => $event,
            'data' => [
                'order_id' => $data['id'],
                'status' => $data['status'],
                'email' => $data['billing']['email'] ?? null,
                'phone' => $data['billing']['phone'] ?? null,
                'total' => (int) $data['total'],

                'items' => collect($data['line_items'])->map(function ($item) {
                    return [
                        'product_id' => $item['product_id'],
                        'name' => $item['name'],
                        'qty' => $item['quantity'],
                        'price' => $item['price'],
                    ];
                })->toArray(),
            ]
        ];
    }
}