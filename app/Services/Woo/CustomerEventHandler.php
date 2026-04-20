<?php

use App\Models\UserWoo;


class CustomerEventHandler
{
    public function handle($data)
    {
        UserWoo::updateOrCreate(
            ['email' => $data['email']],
            [
                'name' => $data['first_name'] . ' ' . $data['last_name'],
                'woo_customer_id' => $data['id']
            ]
        );
    }
}