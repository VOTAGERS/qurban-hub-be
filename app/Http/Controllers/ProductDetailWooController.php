<?php

namespace App\Http\Controllers;

use App\Models\ProductDetailWoo;
use App\Models\ProductWoo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductDetailWooController extends Controller
{
    public function index()
    {
        // Only return details where the master product is not deleted
        $details = ProductDetailWoo::with(['productWoo.fileUpload'])
            ->whereHas('productWoo', function($q) {
                $q->where('status', '!=', 'deleted');
            })
            ->get();
            
        return response()->json([
            'success' => true,
            'data' => $details,
            'message' => 'Product details retrieved successfully'
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'status_woo' => 'required|string|max:50',
            'country' => 'required|string|max:100',
            'max_share' => 'required|integer|min:1',
            'id_fileupload' => 'nullable|integer|exists:file_uploads,id',
        ]);

        try {
            DB::beginTransaction();

            $productWoo = ProductWoo::create([
                'name' => $request->name,
                'price' => $request->price,
                'status' => $request->status_woo,
                'id_fileupload' => $request->id_fileupload,
            ]);

            $detail = ProductDetailWoo::create([
                'idproduct_woo' => $productWoo->id,
                'country' => $request->country,
                'max_share' => $request->max_share,
                'status' => 'A'
            ]);

            DB::commit();

            $detail->load(['productWoo.fileUpload']);

            return response()->json([
                'success' => true,
                'data' => $detail,
                'message' => 'Product and detail created successfully'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $detail = ProductDetailWoo::with(['productWoo.fileUpload'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $detail,
            'message' => 'Product detail retrieved successfully'
        ]);
    }

    public function update(Request $request, $id)
    {
        $detail = ProductDetailWoo::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'status_woo' => 'required|string|max:50',
            'country' => 'required|string|max:100',
            'max_share' => 'required|integer|min:1',
            'id_fileupload' => 'nullable|integer|exists:file_uploads,id',
        ]);

        try {
            DB::beginTransaction();

            // Update ProductWoo
            $productWoo = ProductWoo::findOrFail($detail->idproduct_woo);
            
            $productWoo->update([
                'name' => $request->name,
                'price' => $request->price,
                'status' => $request->status_woo,
                'id_fileupload' => $request->id_fileupload,
            ]);

            // Update Detail
            $detail->update([
                'country' => $request->country,
                'max_share' => $request->max_share,
            ]);

            DB::commit();

            $detail->load(['productWoo.fileUpload']);

            return response()->json([
                'success' => true,
                'data' => $detail,
                'message' => 'Product and detail updated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        $detail = ProductDetailWoo::findOrFail($id);
        
        try {
            DB::beginTransaction();

            // Soft delete the master product woo
            $productWoo = ProductWoo::findOrFail($detail->idproduct_woo);
            $productWoo->update([
                'status' => 'deleted',
                'updated_by' => $request->updated_by ?? 'System'
            ]);

            // Soft delete the detail record
            $detail->update([
                'status' => 'deleted',
                'updated_by' => $request->updated_by ?? 'System'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product and detail soft deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product: ' . $e->getMessage()
            ], 500);
        }
    }
}
