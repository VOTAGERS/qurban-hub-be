<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::where('status', 'active')->orderBy('id_user', 'desc')->get();
        return response()->json([
            'success' => true,
            'data' => $users,
            'message' => 'Users retrieved successfully'
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'phone' => 'required|string',
            'company' => 'nullable|string',
            'address_1' => 'nullable|string',
            'address_2' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'postcode' => 'nullable|string',
            'country' => 'nullable|string',
            'country_code' => 'nullable|string',
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name ?? '',
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'phone' => $request->phone,
            'company' => $request->company ?? '',
            'address_1' => $request->address_1 ?? '',
            'address_2' => $request->address_2 ?? '',
            'city' => $request->city ?? '',
            'state' => $request->state ?? '',
            'postcode' => $request->postcode ?? '',
            'country' => $request->country ?? '',
            'country_code' => $request->country_code ?? 'ID',
            'status' => 'active'
        ]);

        return response()->json([
            'success' => true,
            'data' => $user,
            'message' => 'User created successfully'
        ]);
    }
}
