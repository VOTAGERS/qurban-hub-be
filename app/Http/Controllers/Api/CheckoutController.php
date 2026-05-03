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
            // Basic validation, extend as needed
            $validator = Validator::make($request->all(), [
                'product_id' => 'required|exists:products_woo,id',
                'quantity' => 'required|integer|min:1',
                'total_price' => 'required|numeric|min:0',
                'billing.first_name' => 'required|string|max:255',
                'billing.phone' => 'required|string|max:50',
                'billing.email' => 'nullable|email|max:255',
                // Add more validation rules for billing and shipping if necessary
                'shipping.first_name' => 'required|string|max:255',
                'shipping.phone' => 'required|string|max:50',
                'shipping.email' => 'nullable|email|max:255',
                'recipients' => 'required|array|min:1',
                'recipients.*.qurban_name' => 'required|string|max:255',
                'recipients.*.email' => 'nullable|email|max:255',
                'recipients.*.phone_number' => 'nullable|string|max:50',
                'recipients.*.remarks' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $order = $this->checkoutService->processCheckout($request->all());

            // Create Stripe Checkout Session (Redirect Method)
            if ($request->user()) {
                // Jika login
                $session = $request->user()->checkoutCharge(
                    $order->total_price * 100, 
                    "Pembayaran Qurban #{$order->order_code}", 
                    1, 
                    [
                        'success_url' => config('app.frontend_url') . '/checkout/success?order_code=' . $order->order_code,
                        'cancel_url' => config('app.frontend_url') . '/admin/payment?order_code=' . $order->order_code,
                        'metadata' => ['order_code' => $order->order_code],
                    ]
                );
            } else {
                // Jika guest
                \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
                $session = \Stripe\Checkout\Session::create([
                    'payment_method_types' => ['card'],
                    'line_items' => [[
                        'price_data' => [
                            'currency' => config('cashier.currency') ?? 'sgd',
                            'product_data' => [
                                'name' => "Pembayaran Qurban #{$order->order_code}",
                            ],
                            'unit_amount' => $order->total_price * 100,
                        ],
                        'quantity' => 1,
                    ]],
                    'mode' => 'payment',
                    'success_url' => config('app.frontend_url') . '/checkout/success?order_code=' . $order->order_code,
                    'cancel_url' => config('app.frontend_url') . '/admin/payment?order_code=' . $order->order_code,
                    'metadata' => ['order_code' => $order->order_code],
                ]);
            }

            return response()->json([
                'message' => 'Order created successfully. Redirecting to payment...',
                'order' => $order,
                'checkout_url' => $session->url,
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Log the error
            // Log::error('Checkout processing failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Checkout processing failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
