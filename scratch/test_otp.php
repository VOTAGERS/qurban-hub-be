<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

use Illuminate\Http\Request;

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Request::create('/api/auth/verify-otp', 'POST', [
    'email' => 'admin@test',
    'otp_code' => '605204'
]);
$request->headers->set('Accept', 'application/json');

$response = $kernel->handle($request);
echo $response->getContent();
