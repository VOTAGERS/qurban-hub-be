<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function index()
    {
        $deliveries = Delivery::where('status', 'A')->get();
        return response()->json([
            'success' => true,
            'data' => $deliveries,
            'message' => 'Deliveries retrieved successfully'
        ]);
    }
}
