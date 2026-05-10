<?php

namespace App\Http\Controllers;

use App\Models\ProductWoo;

class ProductWooController extends Controller
{
    public function index()
    {
        $products = ProductWoo::with('productDetail')
            ->where('status', 'publish')
            ->whereHas('productDetail', function($query) {
                $query->where('status', 'active');
            })
            ->get();
        return response()->json([
            'success' => true,
            'data' => $products,
            'message' => 'WooCommerce products with details retrieved successfully'
        ]);
    }
}
