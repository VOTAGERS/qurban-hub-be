<?php

use App\Models\User;
use App\Models\ProductWoo;
use App\Models\Order;
use App\Mail\QurbanThankYouMail;
use Illuminate\Support\Facades\Mail;

// Ensure we have a user
$user = User::firstOrCreate(
    ['email' => 'ganesyudhakusuma@gmail.com'],
    [
        'first_name' => 'Ganes',
        'phone' => '08123456789',
        'status' => 'active'
    ]
);

// Ensure we have a product
$product = ProductWoo::firstOrCreate(
    ['name' => 'Sapi Qurban Premium'],
    [
        'price' => 5000000,
        'status' => 'active'
    ]
);

// Create a dummy order
$order = Order::create([
    'order_code' => 'ORD-TEST-' . strtoupper(Str::random(6)),
    'id_user' => $user->id_user,
    'idproduct_woo' => $product->id,
    'quantity' => 1,
    'total_price' => 5000000,
    'payment_status' => 'paid',
    'qurban_status' => 'scheduled',
    'status' => 'active',
    'created_by' => 'SYSTEM',
    'updated_by' => 'SYSTEM'
]);

// Send the mail
Mail::to('ganesyudhakusuma@gmail.com')->send(new QurbanThankYouMail($order->load(['user', 'productWoo'])));

echo "Email successfully sent to ganesyudhakusuma@gmail.com for Order #{$order->order_code}\n";
