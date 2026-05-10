<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['user', 'productWoo.productDetail', 'participants.certificate'])
            ->whereIn('status', ['A', 'active'])
            ->get();
            
        return response()->json([
            'success' => true,
            'data' => $orders,
            'message' => 'Orders retrieved successfully'
        ]);
    }

    public function byUser($userId)
    {
        $currentUser = auth('sanctum')->user();
        $isSuperAdmin = $currentUser ? $currentUser->isSuperAdmin() : false;
        
        $query = Order::with(['user', 'productWoo.productDetail', 'participants.certificate'])
            ->whereIn('status', ['A', 'active'])
            ->orderBy('id_order', 'desc');

        // Jika user adalah SuperAdmin/Admin dan sedang mengakses data "My Order" (ID dirinya sendiri), 
        // berikan akses ke seluruh data (Full Access) sesuai permintaan.
        if ($currentUser && $isSuperAdmin && $userId == $currentUser->id_user) {
            // No id_user filter = Full Access
        } else {
            // Filter berdasarkan userId (untuk user biasa atau Admin yang melihat user tertentu)
            $query->where('id_user', $userId);
        }

        $orders = $query->get();
            
        return response()->json([
            'success' => true,
            'data' => $orders,
            'message' => 'User orders retrieved successfully'
        ]);
    }
}
