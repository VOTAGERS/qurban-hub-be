<?php

namespace App\Http\Controllers;

use App\Models\OrderWoo;
use App\Models\OrderItemWoo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderWooController extends Controller
{
    public function index()
    {
        $orders = OrderWoo::with('items')->get();
        return response()->json([
            'status' => 'success',
            'data' => $orders
        ]);
    }

    public function store(Request $request)
    {
        // Gunakan struktur payload yang sama dengan WooCommerce
        app(\App\Services\Woo\OrderEventHandler::class)->handle($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Order processed using existing OrderEventHandler'
        ], 201);
    }

    public function show($id)
    {
        $order = OrderWoo::with('items')->findOrFail($id);
        return response()->json([
            'status' => 'success',
            'data' => $order
        ]);
    }
}
