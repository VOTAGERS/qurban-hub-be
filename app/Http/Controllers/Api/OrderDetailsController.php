<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderDetailsController extends Controller
{
    public function show(string $orderCode)
    {
        try {
            $order = Order::where('order_code', $orderCode)
                ->with(['user', 'productWoo', 'participants', 'billing.user', 'shipping.user'])
                ->first();

            if (!$order) {
                return response()->json(['message' => 'Order not found'], 404);
            }

            return response()->json([
                'message' => 'Order details retrieved successfully',
                'data' => $order,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error fetching order details: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while fetching order details', 'error' => $e->getMessage()], 500);
        }
    }
}
