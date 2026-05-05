<?php

namespace App\Http\Controllers\Api;

use Laravel\Cashier\Http\Controllers\WebhookController as CashierController;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends CashierController
{
    /**
     * Handle payment intent succeeded. (Untuk Stripe Elements)
     *
     * @param  array  $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handlePaymentIntentSucceeded(array $payload)
    {
        $intent = $payload['data']['object'];
        $orderCode = $intent['metadata']['order_code'] ?? null;

        // Log ke apps_log
        \App\Models\AppLog::create([ 
            'data_capture' => json_encode($payload),
            'message' => "Stripe Webhook: payment_intent.succeeded for #{$orderCode}",
            'status' => 'success'
        ]);

        return $this->updateOrderStatus($orderCode, $intent['id']);
    }

    protected function handleCheckoutSessionCompleted(array $payload)
    {
        $session = $payload['data']['object'];
        $orderCode = $session['metadata']['order_code'] ?? null;

        // Log ke apps_log
        \App\Models\AppLog::create([
            'data_capture' => json_encode($payload),
            'message' => "Stripe Webhook: checkout.session.completed for #{$orderCode}",
            'status' => 'success'
        ]);

        return $this->updateOrderStatus($orderCode, $session['id']);
    }

    /**
     * Helper untuk update status order dan simpan ke tabel payments
     */
    protected function updateOrderStatus($orderCode, $stripeId = null)
    {
        if ($orderCode) {
            $order = Order::where('order_code', $orderCode)->first();

            if ($order) {
                \Illuminate\Support\Facades\DB::transaction(function () use ($order, $stripeId) {
                    // 1. Update status order
                    $order->update([
                        'payment_status' => 'paid',
                        'qurban_status' => 'scheduled'
                    ]);

                    // 2. Simpan ke tabel payments
                    \App\Models\Payment::create([
                        'id_order' => $order->id_order,
                        'payment_method' => 'stripe',
                        'amount' => $order->total_price,
                        'payment_status' => 'paid',
                        'paid_at' => now(),
                        'status' => 'active',
                        'created_by' => $order->id_user ?? 1,
                        'id_stripe' => $stripeId,
                    ]);
                });

                Log::info("Stripe Webhook: Order #{$orderCode} marked as PAID.");
            } else {
                Log::warning("Stripe Webhook: Order #{$orderCode} not found.");
            }
        }

        return $this->successMethod();
    }
}
