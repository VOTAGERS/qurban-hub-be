<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

use App\Http\Controllers\UserController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderParticipantController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\QurbanExecutionController;
use App\Http\Controllers\QurbanMediaController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\WebhookController;




Route::post('/webhook/woocommerce', [WebhookController::class, 'handle']);

Route::prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'index']);
});

Route::prefix('packages')->group(function () {
    Route::get('/', [PackageController::class, 'index']);
    Route::post('/', [PackageController::class, 'store']);
    Route::get('/{id}', [PackageController::class, 'show']);
    Route::put('/{id}', [PackageController::class, 'update']);
    Route::delete('/{id}', [PackageController::class, 'destroy']);
});

Route::prefix('orders')->group(function () {
    Route::get('/', [OrderController::class, 'index']);
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

Route::get('/test', function () {
    return response()->json([
        'message' => 'Connection to Backend Successful!',
        'status' => 'success'
    ]);
});
