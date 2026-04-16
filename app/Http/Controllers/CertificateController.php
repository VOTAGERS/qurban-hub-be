<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    public function index()
    {
        $certificates = Certificate::where('status', 'A')->get();
        return response()->json([
            'success' => true,
            'data' => $certificates,
            'message' => 'Certificates retrieved successfully'
        ]);
    }
}
