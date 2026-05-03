<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Order;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::where('status', 'A')->get();
        return response()->json([
            'success' => true,
            'data' => $payments,
            'message' => 'Payments retrieved successfully'
        ]);
    }

    public function checkout(Request $request)
    {
        $order = Order::where('order_code', $request->order_code)->first();
        
        if (!$order) return redirect()->to(config('app.frontend_url'));

        // Jika User Login, gunakan Cashier
        if ($request->user()) {
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
            // Jika GUEST (Tanpa Login), gunakan Stripe SDK langsung
            $session = Session::create([
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
            'url' => $session->url,
        ]);
    }
}
