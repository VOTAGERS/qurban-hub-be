<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RoleAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoleAccessController extends Controller
{
    public function index()
    {
        $roles = RoleAccess::all();
        return response()->json([
            'success' => true,
            'data' => $roles
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role_name' => 'required|string|unique:role_accesses,role_name',
            'status' => 'nullable|string',
            'created_by' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $role = RoleAccess::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Role created successfully',
            'data' => $role
        ], 201);
    }

    public function show($id)
    {
        $role = RoleAccess::find($id);

        if (!$role) {
            return response()->json(['message' => 'Role not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $role
        ]);
    }

    public function update(Request $request, $id)
    {
        $role = RoleAccess::find($id);

        if (!$role) {
            return response()->json(['message' => 'Role not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'role_name' => 'string|unique:role_accesses,role_name,' . $id . ',id_role_access',
            'status' => 'string',
            'updated_by' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $role->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Role updated successfully',
            'data' => $role
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $role = RoleAccess::find($id);

        if (!$role) {
            return response()->json(['message' => 'Role not found'], 404);
        }

        // Manual Soft Delete: Update status, user, and timestamp
        $role->update([
            'status' => 'deleted',
            'updated_by' => $request->updated_by ?? 'System' // Mengambil info user jika ada
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Role status set to deleted successfully',
            'data' => $role
        ]);
    }
}
