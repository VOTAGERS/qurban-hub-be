<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

use Illuminate\Http\Request;

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Ganti ID 3 dengan ID order yang valid jika perlu
$orderId = 3; 

echo "--- Testing Certificate Generation for Order ID: $orderId ---\n";

$request = Request::create("/api/certificates/order/$orderId/generate-bulk", 'POST');
$request->headers->set('Accept', 'application/json');

$response = $kernel->handle($request);

echo "Response Body: \n";
echo $response->getContent();
echo "\n\n--- Done ---\n";
