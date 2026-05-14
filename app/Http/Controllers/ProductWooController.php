<?php

namespace App\Http\Controllers;

use App\Models\ProductWoo;

class ProductWooController extends Controller
{
    public function index()
    {
        $products = ProductWoo::with(['productDetail', 'fileUpload'])
            ->where('status', 'publish')
            ->whereHas('productDetail', function($query) {
                $query->where('status', 'active');
            })
            ->get();
        return response()->json([
            'success' => true,
            'data' => $products,
            'message' => 'Products retrieved successfully'
        ]);
    }

    public function store(\Illuminate\Http\Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name'          => 'required|string|max:255',
            'price'         => 'required|numeric',
            'status'        => 'required|string',
            'id_fileupload' => 'nullable|exists:file_uploads,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $product = ProductWoo::create([
            'name'          => $request->name,
            'price'         => $request->price,
            'status'        => $request->status,
            'id_fileupload' => $request->id_fileupload,
            'created_by'    => auth()->user()->email ?? 'SYSTEM',
            'updated_by'    => 'SYSTEM'
        ]);

        return response()->json(['success' => true, 'data' => $product], 201);
    }

    public function show($id)
    {
        $product = ProductWoo::with(['productDetail', 'fileUpload'])->findOrFail($id);
        return response()->json(['success' => true, 'data' => $product]);
    }

    public function update(\Illuminate\Http\Request $request, $id)
    {
        $product = ProductWoo::findOrFail($id);
        
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name'          => 'sometimes|string|max:255',
            'price'         => 'sometimes|numeric',
            'status'        => 'sometimes|string',
            'id_fileupload' => 'nullable|exists:file_uploads,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $product->update($request->all());

        return response()->json(['success' => true, 'data' => $product]);
    }

    public function destroy($id)
    {
        $product = ProductWoo::findOrFail($id);
        $product->update([
            'status' => 'deleted',
            'updated_by' => auth('sanctum')->user()->email ?? 'System'
        ]);
        return response()->json(['success' => true, 'message' => 'Product marked as deleted']);
    }
}
