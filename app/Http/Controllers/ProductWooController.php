<?php

namespace App\Http\Controllers;

use App\Models\ProductWoo;

class ProductWooController extends Controller
{
    public function index()
    {
        $products = ProductWoo::with('productDetail')->get();
        return response()->json([
            'success' => true,
            'data' => $products,
            'message' => 'WooCommerce products with details retrieved successfully'
        ]);
    }
}
