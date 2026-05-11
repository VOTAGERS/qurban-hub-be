<?php

use App\Models\Order;
use App\Models\User;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $userId = 7;
    $query = Order::with(['user', 'productWoo.productDetail', 'participants.certificate'])
        ->orderBy('id_order', 'desc')
        ->where('id_user', $userId);

    $orders = $query->get();
    echo "Success! Found " . $orders->count() . " orders.\n";
    if ($orders->count() > 0) {
        $firstOrder = $orders->first()->toArray();
        echo "Keys: " . implode(', ', array_keys($firstOrder)) . "\n";
        if (isset($firstOrder['product_woo'])) {
            echo "product_woo found!\n";
        } elseif (isset($firstOrder['productWoo'])) {
            echo "productWoo found!\n";
        }
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
