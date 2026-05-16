<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Hashids\Hashids;
use App\Models\Payment;


class OrderController extends Controller
{
    public function index()
    {
       $orders = Order::with(['user', 'productWoo.productDetail', 'participants.certificate', 'payment'])->whereIn('status', ['A', 'active'])->get();
            
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
        $isAdmin = $currentUser ? $currentUser->isAdmin() : false;
        
        $query = Order::with(['user', 'productWoo.productDetail', 'participants.certificate'])
            ->whereIn('status', ['A', 'active'])
            ->orderBy('id_order', 'desc');

        // Jika user adalah SuperAdmin/Admin dan sedang mengakses data "My Order" (ID dirinya sendiri), 
        // berikan akses ke seluruh data (Full Access) sesuai permintaan.
        if ($currentUser && ($isSuperAdmin || $isAdmin) && $userId == $currentUser->id_user) {
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

    public function updateStatus(Request $request, string $hash)
    {
        $request->validate([
            'payment_status' => 'required|in:paid,pending,failed'
        ]);
        $hashids = new Hashids(config('hashids.connections.alternative.salt'), 12);
        $decoded = $hashids->decode($hash);

        if (empty($decoded)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid payment ID'
            ], 400);
        }

        $payment = Payment::findOrFail($decoded[0]);

        if ($payment->payment_method !== 'bank_transfer') {
            return response()->json([
                'success' => false,
                'message' => 'bank suskes terupdatee'
            ], 403);
        }
        $payment->payment_status = $request->payment_status;
        if ($request->payment_status === 'paid') {
            $payment->paid_at = now();
        }
        $payment->updated_by = auth('sanctum')->user()->email ?? 'SYSTEM';
        $payment->save();
        if ($payment->order) {
          $payment->order->payment_status = $request->payment_status;
        if ($request->payment_status === 'paid') {
            $payment->order->qurban_status = 'scheduled';
        } elseif ($request->payment_status === 'failed' || $request->payment_status === 'pending') {
            $payment->order->qurban_status = 'pending';
        }
        $payment->order->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'pembayran suskses'
        ]);
    }


}
