<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessWooWebhookJob;
use Illuminate\Http\Request;




class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        $event = $request->header('X-WC-Webhook-Topic');
        $payload = $request->all();

        // Dispatch ke queue biar cepat
        dispatch(new ProcessWooWebhookJob($event, $payload));

        return response()->json(['ok']);
    }
}



