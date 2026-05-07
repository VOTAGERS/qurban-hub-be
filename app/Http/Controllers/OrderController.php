<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['user', 'productWoo'])->where('status', 'active')->get();
        return response()->json([
            'success' => true,
            'data' => $orders,
            'message' => 'Orders retrieved successfully'
        ]);
    }

    public function byUser($userId)
    {
        $orders = Order::with(['user', 'productWoo'])
            ->where('id_user', $userId)
            ->where('status', 'active')
            ->orderBy('id_order', 'desc')
            ->get();
            
        return response()->json([
            'success' => true,
            'data' => $orders,
            'message' => 'User orders retrieved successfully'
        ]);
    }
}
