<?php

namespace App\Http\Controllers;

use App\Models\QurbanMedia;
use Illuminate\Http\Request;

class QurbanMediaController extends Controller
{
    public function index()
    {
        $media = QurbanMedia::where('status', 'A')->get();
        return response()->json([
            'success' => true,
            'data' => $media,
            'message' => 'Qurban media retrieved successfully'
        ]);
    }
}
