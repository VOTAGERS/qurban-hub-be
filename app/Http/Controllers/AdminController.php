<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $admins = Admin::where('status', 'A')->get();
        return response()->json([
            'success' => true,
            'data' => $admins,
            'message' => 'Admins retrieved successfully'
        ]);
    }
}
