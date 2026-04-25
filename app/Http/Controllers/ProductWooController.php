<?php

namespace App\Http\Controllers;

use App\Models\ProductWoo;

class ProductWooController extends Controller
{
    public function index()
    {
        // Typically we only want to list published products or all products for admin
        $products = ProductWoo::all();
        return response()->json([
            'success' => true,
            'data' => $products,
            'message' => 'WooCommerce products retrieved successfully'
        ]);
    }
}
