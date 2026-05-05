<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessWooWebhookJob;
use Illuminate\Http\Request;
use App\Services\Woo\WooPayloadMapper;




class WebhookController extends Controller
{ 
    public function handle(Request $request) 
    {
        \Log::info('Webhook masuk', $request->all());

        $event = $request->header('X-WC-Webhook-Topic') ?? 'ping';
        $payload = $request->all();

        if ($event === 'ping' || isset($payload['webhook_id'])) {
            return response()->json(['message' => 'Ping received successfully'], 200);
        }

        $mapped = \WooPayloadMapper::mapOrder($event, $payload);

        \Log::info('Mapped payload', $mapped);

        dispatch(new ProcessWooWebhookJob($mapped));

        return response()->json(['ok']);
    }

}



