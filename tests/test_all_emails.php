<?php

use App\Models\User;
use App\Models\ProductWoo;
use App\Models\Order;
use App\Mail\QurbanThankYouMail;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

$email = 'ganesyudhakusuma@gmail.com';

// 1. Send OTP Mail
echo "Sending OTP Mail...\n";
Mail::to($email)->send(new OtpMail('123456'));

// 2. Send Thank You Mail
echo "Sending Thank You Mail...\n";
$user = User::firstOrCreate(
    ['email' => $email],
    ['first_name' => 'Ganes', 'phone' => '08123456789', 'status' => 'active']
);

$product = ProductWoo::firstOrCreate(
    ['name' => 'Sapi Qurban Premium'],
    ['price' => 5000000, 'status' => 'active']
);

$order = Order::create([
    'order_code' => 'ORD-MAROON-' . strtoupper(Str::random(4)),
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

Mail::to($email)->send(new QurbanThankYouMail($order->load(['user', 'productWoo'])));

echo "Both emails sent successfully to {$email}!\n";
