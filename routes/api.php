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
use App\Http\Controllers\Api\CertificateController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\ProductWooController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\OrderDetailsController;
use App\Http\Controllers\Api\RoleAccessController;
use App\Http\Controllers\Api\StripeWebhookController;
use App\Http\Controllers\Api\UserAccessController;
use App\Http\Controllers\Api\AuthController;


/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Checkout Flow
Route::post('/checkout', [CheckoutController::class, 'process']);
Route::post('/create-checkout-session', [PaymentController::class, 'checkout']);
Route::get('/order-details/{orderCode}', [OrderDetailsController::class, 'show']);

// Webhooks
Route::post('/webhook/woocommerce', [WebhookController::class, 'handle']);
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook']);

// Health Check
Route::get('/test', function () {
    return response()->json([
        'message' => 'Connection to Backend Successful!',
        'status' => 'success'
    ]);
});

Route::post('/checkout/create-payment-intent', [CheckoutController::class, 'createPaymentIntent']);
Route::post('/checkout/confirm-payment',[CheckoutController::class, 'confirmPayment']);
Route::post('/checkout/create-bank-transfer-order', [CheckoutController::class, 'createBankTransferOrder']);
Route::get('/certificates/public/download/{participantId}', [CertificateController::class, 'publicDownload']);


// Auth Routes
Route::prefix('auth')->group(function () {
    Route::post('/send-otp', [AuthController::class, 'sendOtp']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/login-password', [AuthController::class, 'loginWithPassword']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

/*
|--------------------------------------------------------------------------
| Resource Routes (Public for now, adjust middleware as needed)
|--------------------------------------------------------------------------
*/

// Role Management
Route::prefix('role-access')->group(function () {
    Route::get('/', [RoleAccessController::class, 'index']);
    Route::post('/', [RoleAccessController::class, 'store']);
    Route::get('/{id}', [RoleAccessController::class, 'show']);
    Route::put('/{id}', [RoleAccessController::class, 'update']);
    Route::delete('/{id}', [RoleAccessController::class, 'destroy']);
});

// User Access Management
Route::prefix('user-access')->group(function () {
    Route::get('/', [UserAccessController::class, 'index']);
    Route::post('/', [UserAccessController::class, 'store']);
    Route::get('/user/{userId}', [UserAccessController::class, 'show']);
    Route::delete('/{id}', [UserAccessController::class, 'destroy']);
});

// WooCommerce Sync Resources
Route::prefix('products-woo')->group(function () {
    Route::get('/', [ProductWooController::class, 'index']);
});

Route::prefix('products-detail')->group(function () {
    Route::get('/', [ProductDetailWooController::class, 'index']);
    Route::post('/', [ProductDetailWooController::class, 'store']);
    Route::get('/{id}', [ProductDetailWooController::class, 'show']);
    Route::put('/{id}', [ProductDetailWooController::class, 'update']);
    Route::delete('/{id}', [ProductDetailWooController::class, 'destroy']);
});

// Internal Resources
Route::prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::post('/', [UserController::class, 'store']);
    Route::put('/{id}', [UserController::class, 'update']);
    Route::delete('/{id}', [UserController::class, 'destroy']);
});

Route::prefix('admins')->group(function () {
    Route::get('/', [AdminController::class, 'index']);
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
    Route::get('/orders', [CertificateController::class, 'getOrdersForCertificates']);
    Route::get('/order/{id}/participants', [CertificateController::class, 'getOrderParticipants']);
    Route::post('/order/{id}/generate-bulk', [CertificateController::class, 'bulkGenerate']);
    Route::get('/download/{id}', [CertificateController::class, 'download']);
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
