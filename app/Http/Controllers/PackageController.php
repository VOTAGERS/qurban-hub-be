<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function index()
    {
        $packages = Package::where('status', 'A')->get();
        return response()->json([
            'success' => true,
            'data' => $packages,
            'message' => 'Packages retrieved successfully'
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'animal_type' => 'required|in:goat,sheep,cow',
            'country' => 'required|string|max:100',
            'price' => 'required|numeric',
            'max_share' => 'integer|min:1',
            'description' => 'nullable|string',
        ]);

        $package = Package::create($validated);

        return response()->json([
            'success' => true,
            'data' => $package,
            'message' => 'Package created successfully'
        ], 201);
    }

    public function show($id)
    {
        $package = Package::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $package
        ]);
    }

    public function update(Request $request, $id)
    {
        $package = Package::findOrFail($id);

        $validated = $request->validate([
            'animal_type' => 'sometimes|required|in:goat,sheep,cow',
            'country' => 'sometimes|required|string|max:100',
            'price' => 'sometimes|required|numeric',
            'max_share' => 'sometimes|integer|min:1',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:active,inactive,deleted',
        ]);

        $package->update($validated);

        return response()->json([
            'success' => true,
            'data' => $package,
            'message' => 'Package updated successfully'
        ]);
    }

    public function destroy($id)
    {
        $package = Package::findOrFail($id);

        // Soft delete: just change status to deleted
        $package->update(['status' => 'N']);

        return response()->json([
            'success' => true,
            'message' => 'Package soft-deleted successfully'
        ]);
    }
}
