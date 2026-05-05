<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserRole;
use App\Models\User;
use App\Models\RoleAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserAccessController extends Controller
{
    public function index()
    {
        $userRoles = UserRole::where('status', 'active')
            ->with(['user', 'role'])
            ->get();
            
        return response()->json([
            'success' => true,
            'data' => $userRoles
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_user' => 'required|exists:users,id_user',
            'role_codes' => 'required|array',
            'role_codes.*' => 'exists:role_accesses,role_code',
            'created_by' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $userId = $request->id_user;
        $newRoleCodes = $request->role_codes;
        $createdBy = $request->created_by ?? 'System';

        // Get current active roles for this user
        $currentRoles = UserRole::where('id_user', $userId)
            ->where('status', 'active')
            ->pluck('role_code')
            ->toArray();

        // Roles to add (in new but not in current)
        $toAdd = array_diff($newRoleCodes, $currentRoles);
        
        // Roles to remove (in current but not in new)
        $toRemove = array_diff($currentRoles, $newRoleCodes);

        // Add new roles
        foreach ($toAdd as $code) {
            // Check if there's a deleted record we can reactivate
            $existing = UserRole::where('id_user', $userId)->where('role_code', $code)->first();
            if ($existing) {
                $existing->update(['status' => 'active', 'updated_by' => $createdBy]);
            } else {
                UserRole::create([
                    'id_user' => $userId,
                    'role_code' => $code,
                    'status' => 'active',
                    'created_by' => $createdBy
                ]);
            }
        }

        // Remove (soft delete) roles no longer selected
        if (!empty($toRemove)) {
            UserRole::where('id_user', $userId)
                ->whereIn('role_code', $toRemove)
                ->update(['status' => 'deleted', 'updated_by' => $createdBy]);
        }

        return response()->json([
            'success' => true,
            'message' => 'User access updated successfully',
        ], 200);
    }

    public function show($userId)
    {
        $roles = UserRole::where('id_user', $userId)
            ->where('status', 'active')
            ->with('role')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $roles
        ]);
    }

    public function destroy($id)
    {
        $userRole = UserRole::find($id);

        if (!$userRole) {
            return response()->json(['message' => 'User role assignment not found'], 404);
        }

        $userRole->update(['status' => 'deleted']);

        return response()->json([
            'success' => true,
            'message' => 'Role assignment removed successfully'
        ]);
    }
}
