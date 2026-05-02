<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductDetailWooController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderParticipantController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\QurbanExecutionController;
use App\Http\Controllers\QurbanMediaController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\ProductWooController;
use App\Http\Controllers\UserWooController;
use App\Http\Controllers\OrderWooController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\OrderDetailsController;

Route::post('/webhook/woocommerce', [WebhookController::class, 'handle']);

Route::prefix('products-woo')->group(function () {
    Route::get('/', [ProductWooController::class, 'index']);
    Route::post('/', [ProductWooController::class, 'store']);
    Route::get('/{id}', [ProductWooController::class, 'show']);
});

Route::prefix('users-woo')->group(function () {
    Route::get('/', [UserWooController::class, 'index']);
    Route::post('/', [UserWooController::class, 'store']);
    Route::get('/{id}', [UserWooController::class, 'show']);
});

Route::prefix('orders-woo')->group(function () {
    Route::get('/', [OrderWooController::class, 'index']);
    Route::post('/', [OrderWooController::class, 'store']);
    Route::get('/{id}', [OrderWooController::class, 'show']);
});

Route::prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'index']);
});

Route::prefix('products-detail')->group(function () {
    Route::get('/', [ProductDetailWooController::class, 'index']);
    Route::post('/', [ProductDetailWooController::class, 'store']);
    Route::get('/{id}', [ProductDetailWooController::class, 'show']);
    Route::put('/{id}', [ProductDetailWooController::class, 'update']);
    Route::delete('/{id}', [ProductDetailWooController::class, 'destroy']);
});

Route::prefix('products-woo')->group(function () {
    Route::get('/', [ProductWooController::class, 'index']);
});

Route::prefix('orders')->group(function () {
    Route::get('/', [OrderController::class, 'index']);
    Route::get('/user/{userId}', [OrderController::class, 'byUser']);
});

Route::prefix('order-participants')->group(function () {
    Route::get('/', [OrderParticipantController::class, 'index']);
});

Route::prefix('payments')->group(function () {
    Route::get('/', [PaymentController::class, 'index']);
});

Route::prefix('qurban-executions')->group(function () {
    Route::get('/', [QurbanExecutionController::class, 'index']);
});

Route::prefix('qurban-media')->group(function () {
    Route::get('/', [QurbanMediaController::class, 'index']);
});

Route::prefix('certificates')->group(function () {
    Route::get('/', [CertificateController::class, 'index']);
});

Route::prefix('deliveries')->group(function () {
    Route::get('/', [DeliveryController::class, 'index']);
});

Route::prefix('admins')->group(function () {
    Route::get('/', [AdminController::class, 'index']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/checkout', [CheckoutController::class, 'process']);

// New route for fetching order details
Route::get('/order-details/{orderCode}', [OrderDetailsController::class, 'show']);

Route::get('/test', function () {
    return response()->json([
        'message' => 'Connection to Backend Successful!',
        'status' => 'success'
    ]);
});
