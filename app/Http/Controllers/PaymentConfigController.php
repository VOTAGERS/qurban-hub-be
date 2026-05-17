<?php

namespace App\Http\Controllers;

use App\Models\PaymentConfig;
use Illuminate\Http\Request;
use Stripe\StripeClient;

class PaymentConfigController extends Controller
{
    public function index()
    {
        $config = PaymentConfig::where('provider', 'stripe')->first();

        if (!$config) {
            return response()->json(['success' => true, 'data' => null]);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'id'             => $config->id,
                'public_key'     => $config->public_key,
                'secret_key'     => $this->maskKey($config->secret_key),
                'webhook_secret' => $config->webhook_secret ? $this->maskKey($config->webhook_secret) : '',
                'mode'           => $config->mode,
                'is_active'      => $config->is_active,
                'webhook_url'    => $config->webhook_url,
                'has_webhook'    => !empty($config->webhook_secret),
            ]
        ]);
    }

    public function save(Request $request)
    {
        $request->validate([
            'public_key' => 'required|string|starts_with:pk_',
            'mode'       => 'required|in:test,live',
        ]);

        $existing = PaymentConfig::where('provider', 'stripe')->first();
        $webhookUrl = url('/api/stripe/webhook');

        $data = [
            'public_key'  => trim($request->public_key),
            'webhook_url' => $webhookUrl,
            'mode'        => $request->mode,
            'is_active'   => true,
            'updated_by'  => auth()->user()->email ?? 'system',
        ];

        $secretKey = trim($request->secret_key ?? '');
        $isNewKey  = $secretKey && str_starts_with($secretKey, 'sk_') && !str_contains($secretKey, '*');

        if ($isNewKey) {
            $data['secret_key'] = $secretKey;
        } elseif (!$existing) {
            return response()->json([
                'success' => false,
                'message' => 'Secret key is required for first time setup.'
            ], 422);
        }

        // Handle manual Webhook Secret if provided
        $webhookSecret = trim($request->webhook_secret ?? '');
        $isNewWebhookSecret = $webhookSecret && str_starts_with($webhookSecret, 'whsec_') && !str_contains($webhookSecret, '*');
        if ($isNewWebhookSecret) {
            $data['webhook_secret'] = $webhookSecret;
        }

        $config = PaymentConfig::updateOrCreate(
            ['provider' => 'stripe'],
            $data
        );

        // Only auto-register webhook if webhook_secret wasn't manually provided
        $webhookResult = null;
        if (!$isNewWebhookSecret && empty($config->webhook_secret)) {
            $webhookResult = $this->registerWebhook($config);
        } else {
            $webhookResult = [
                'status' => 'already_exists',
                'reason' => 'Webhook secret provided manually or already exists.'
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Configuration saved successfully.',
            'webhook' => $webhookResult,
        ]);
    }
    public function testConnection()
    {
        $config = PaymentConfig::where('provider', 'stripe')->first();

        if (!$config) {
            return response()->json([
                'success' => false,
                'message' => 'Configuration not found.'
            ], 404);
        }

        try {
            $stripe  = new StripeClient($config->secret_key);
            $balance = $stripe->balance->retrieve();

            return response()->json([
                'success' => true,
                'message' => 'Connection successful!',
                'mode'    => $balance->livemode ? 'live' : 'test',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
            ], 400);
        }
    }
    public function deleteWebhook()
    {
        $config = PaymentConfig::where('provider', 'stripe')->first();

        if (!$config || !$config->webhook_stripe_id) {
            return response()->json([
                'success' => false,
                'message' => 'No webhook found.'
            ], 404);
        }

        try {
            $stripe = new StripeClient($config->secret_key);
            $stripe->webhookEndpoints->delete($config->webhook_stripe_id);

            $config->update([
                'webhook_secret'    => null,
                'webhook_stripe_id' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Webhook deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete webhook: ' . $e->getMessage(),
            ], 400);
        }
    }

    private function registerWebhook(PaymentConfig $config): array
    {
        try {
            $stripe     = new StripeClient($config->secret_key);
            $webhookUrl = url('/api/stripe/webhook');

            if ($config->webhook_stripe_id) {
                try {
                    $stripe->webhookEndpoints->delete($config->webhook_stripe_id);
                } catch (\Exception $e) {
                }
            }

            $webhook = $stripe->webhookEndpoints->create([
                'url'            => $webhookUrl,
                'enabled_events' => [
                    'payment_intent.succeeded',
                    'payment_intent.payment_failed',
                    'checkout.session.completed',
                ],
            ]);

            $config->webhook_secret    = $webhook->secret;
            $config->webhook_stripe_id = $webhook->id;
            $config->save();

            return [
                'status' => 'created',
                'url'    => $webhookUrl,
                'id'     => $webhook->id,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'failed',
                'reason' => $e->getMessage(),
            ];
        }
    }

    private function maskKey(string $key): string
    {
        if (strlen($key) <= 8) return '********';
        return substr($key, 0, 12) . str_repeat('*', strlen($key) - 16) . substr($key, -4);
    }
}
