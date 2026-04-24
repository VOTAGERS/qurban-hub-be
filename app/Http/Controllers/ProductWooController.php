<?php

namespace App\Http\Controllers;

use App\Models\ProductWoo;
use Illuminate\Http\Request;

class ProductWooController extends Controller
{
    public function index()
    {
        $products = ProductWoo::all();
        return response()->json([
            'status' => 'success',
            'data' => $products
        ]);
    }

    public function store(Request $request)
    {
        app(\App\Services\Woo\ProductEventHandler::class)->handle($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Product processed using existing ProductEventHandler'
        ], 201);
    }

    public function show($id)
    {
        $product = ProductWoo::findOrFail($id);
        return response()->json([
            'status' => 'success',
            'data' => $product
        ]);
    }
}
