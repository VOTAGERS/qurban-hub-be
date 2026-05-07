<?php

namespace App\Http\Controllers;

use App\Models\QurbanExecution;
use Illuminate\Http\Request;

class QurbanExecutionController extends Controller
{
    public function index()
    {
        $executions = QurbanExecution::where('status', 'active')->get();
        return response()->json([
            'success' => true,
            'data' => $executions,
            'message' => 'Qurban executions retrieved successfully'
        ]);
    }
}
