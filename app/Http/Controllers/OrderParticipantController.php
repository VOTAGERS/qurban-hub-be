<?php

namespace App\Http\Controllers;

use App\Models\OrderParticipant;
use Illuminate\Http\Request;

class OrderParticipantController extends Controller
{
    public function index()
    {
        $participants = OrderParticipant::whereIn('status', ['A', 'active'])->get();
        return response()->json([
            'success' => true,
            'data' => $participants,
            'message' => 'Order participants retrieved successfully'
        ]);
    }
}
