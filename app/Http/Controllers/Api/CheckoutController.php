<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\CheckoutService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    protected $checkoutService;

    public function __construct(CheckoutService $checkoutService)
    {
        $this->checkoutService = $checkoutService;
    }

    public function process(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'product_id'                  => 'required|exists:products_woo,id',
                'quantity'                    => 'required|integer|min:1',
                'total_price'                 => 'required|numeric|min:0',
                'billing.first_name'          => 'required|string|max:255',
                'billing.phone'               => 'required|string|max:50',
                'billing.email'               => 'nullable|email|max:255',
                'shipping.first_name'         => 'required|string|max:255',
                'shipping.phone'              => 'required|string|max:50',
                'shipping.email'              => 'nullable|email|max:255',
                'recipients'                  => 'required|array|min:1',
                'recipients.*.qurban_name'    => 'required|string|max:255',
                'recipients.*.email'          => 'nullable|email|max:255',
                'recipients.*.phone_number'   => 'nullable|string|max:50',
                'recipients.*.remarks'        => 'nullable|string',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $order = $this->checkoutService->processCheckout($request->all());

            if ($request->user()) {
                $session = $request->user()->checkoutCharge(
                    $order->total_price * 100,
                    "Pembayaran Qurban #{$order->order_code}",
                    1,
                    [
                        'success_url' => config('app.frontend_url') . '/',
                        'cancel_url'  => config('app.frontend_url') . '/admin/payment?order_code=' . $order->order_code,
                        'metadata'    => ['order_code' => $order->order_code],
                    ]
                );
            } else {
                \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
                $session = \Stripe\Checkout\Session::create([
                    'payment_method_types' => ['card'],
                    'line_items' => [[
                        'price_data' => [
                            'currency'     => config('cashier.currency') ?? 'sgd',
                            'product_data' => ['name' => "Pembayaran Qurban #{$order->order_code}"],
                            'unit_amount'  => $order->total_price * 100,
                        ],
                        'quantity' => 1,
                    ]],
                    'mode'        => 'payment',
                    'success_url' => config('app.frontend_url') . '/?status=success&order_code=' . $order->order_code,
                    'cancel_url'  => config('app.frontend_url') . '/admin/payment?order_code=' . $order->order_code,
                    'metadata'    => ['order_code' => $order->order_code],
                ]);
            }

            return response()->json([
                'message'      => 'Order created successfully. Redirecting to payment...',
                'order'        => $order,
                'checkout_url' => $session->url,
            ], 201);

        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validation Error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Checkout processing failed', 'error' => $e->getMessage()], 500);
        }
    }

    public function createPaymentIntent(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'product_id'                  => 'required|exists:products_woo,id',
                'quantity'                    => 'required|integer|min:1',
                'total_price'                 => 'required|numeric|min:0',
                'billing.first_name'          => 'required|string|max:255',
                'billing.phone'               => 'required|string|max:50',
                'billing.email'               => 'nullable|email|max:255',
                'shipping.first_name'         => 'required|string|max:255',
                'shipping.phone'              => 'required|string|max:50',
                'shipping.email'              => 'nullable|email|max:255',
                'recipients'                  => 'required|array|min:1',
                'recipients.*.qurban_name'    => 'required|string|max:255',
                'recipients.*.email'          => 'nullable|email|max:255',
                'recipients.*.phone_number'   => 'nullable|string|max:50',
                'recipients.*.remarks'        => 'nullable|string',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            $order = $this->checkoutService->processCheckout($request->all());
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount'   => $order->total_price * 100,
                'currency' => config('cashier.currency') ?? 'sgd',
                'metadata' => [
                    'order_code' => $order->order_code,
                ],
                'automatic_payment_methods' => [
                    'enabled'          => true,
                    'allow_redirects'  => 'never',
                ],
            ]);

            return response()->json([
                'message'       => 'PaymentIntent created.',
                'order_code'    => $order->order_code,
                'client_secret' => $paymentIntent->client_secret,
            ], 201);

        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validation Error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal membuat PaymentIntent', 'error' => $e->getMessage()], 500);
        }
    }
    public function confirmPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_code'        => 'required|string',
            'payment_intent_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation Error', 'errors' => $validator->errors()], 422);
        }

        try {
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
            $intent = \Stripe\PaymentIntent::retrieve($request->payment_intent_id);
            if ($intent->status !== 'succeeded') {
                return response()->json([
                    'message' => 'Pembayaran belum berhasil.',
                    'status'  => $intent->status,
                ], 422);
            }
            if (($intent->metadata->order_code ?? null) !== $request->order_code) {
                return response()->json(['message' => 'Order code tidak cocok.'], 403);
            }

            $order = \App\Models\Order::where('order_code', $request->order_code)->firstOrFail();
            if ($order->payment_status === 'paid') {
                return response()->json(['message' => 'Order sudah dibayar.', 'order_code' => $order->order_code]);
            }

            \Illuminate\Support\Facades\DB::transaction(function () use ($order, $intent) {
                $order->update([
                    'payment_status' => 'paid',
                    'qurban_status'  => 'scheduled',
                ]);

                \App\Models\Payment::create([
                    'id_order'       => $order->id_order,
                    'payment_method' => 'stripe',
                    'amount'         => $order->total_price,
                    'payment_status' => 'paid',
                    'paid_at'        => now(),
                    'status'         => 'active',
                    'created_by'     => $order->id_user ?? 1,
                    'id_stripe'      => $intent->id,
                ]);
            });

            return response()->json([
                'message'    => 'Payment confirmed successfully.',
                'order_code' => $order->order_code,
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to confirm payment', 'error' => $e->getMessage()], 500);
        }
    }
}