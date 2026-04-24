<?php

namespace App\Http\Controllers;

use App\Models\UserWoo;
use Illuminate\Http\Request;

class UserWooController extends Controller
{
    public function index()
    {
        $users = UserWoo::all();
        return response()->json([
            'status' => 'success',
            'data' => $users
        ]);
    }

    public function store(Request $request)
    {
        app(\App\Services\Woo\CustomerEventHandler::class)->handle($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'User processed using existing CustomerEventHandler'
        ], 201);
    }

    public function show($id)
    {
        $user = UserWoo::findOrFail($id);
        return response()->json([
            'status' => 'success',
            'data' => $user
        ]);
    }
}
