<?php

namespace App\Http\Controllers;

use App\Models\Billing;
use App\Models\Order;
use App\Models\OrderParticipant;
use App\Models\Shipping;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\AppLog;

class CheckoutController extends Controller
{
    public function process(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required',
            'quantity' => 'required|integer|min:1',
            'total_price' => 'required|numeric',
            'buyer' => 'required|array',
            'buyer.firstName' => 'required|string',
            'buyer.lastName' => 'nullable|string',
            'buyer.email' => 'required|email',
            'buyer.phone' => 'required|string',
            'shipping' => 'required|array',
            'same_as_buyer' => 'required|boolean',
            'recipients' => 'required|array|min:1',
            'recipients.*.name' => 'required|string',
            'recipients.*.email' => 'nullable|email',
            'recipients.*.phone' => 'nullable|string',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                // 1. Process Buyer
                $buyerData = $request->buyer;
                $buyerUser = User::updateOrCreate(
                    ['email' => $buyerData['email']],
                    [
                        'first_name' => $buyerData['firstName'],
                        'last_name' => $buyerData['lastName'],
                        'company' => $buyerData['company'] ?? null,
                        'address_1' => $buyerData['address1'] ?? null,
                        'address_2' => $buyerData['address2'] ?? null,
                        'city' => $buyerData['city'] ?? null,
                        'state' => $buyerData['state'] ?? null,
                        'postcode' => $buyerData['postcode'] ?? null,
                        'country' => $buyerData['country'] ?? null,
                        'phone' => $buyerData['phone'],
                    ]
                );

                // 2. Create Order
                $order = Order::create([
                    'order_code' => 'QRN-' . strtoupper(Str::random(8)) . '-' . time(),
                    'id_user' => $buyerUser->id_user,
                    'idproduct_woo' => $request->product_id,
                    'quantity' => $request->quantity,
                    'total_price' => $request->total_price,
                    'payment_status' => 'pending',
                    'qurban_status' => 'pending',
                ]);

                // 3. Save Billing
                Billing::create([
                    'id_order' => $order->id_order,
                    'id_user' => $buyerUser->id_user,
                ]);

                // 4. Process Shipping
                if ($request->same_as_buyer) {
                    Shipping::create([
                        'id_order' => $order->id_order,
                        'id_user' => $buyerUser->id_user,
                    ]);
                } else {
                    $shipData = $request->shipping;
                    $shippingUser = User::updateOrCreate(
                        ['email' => $shipData['email']],
                        [
                            'first_name' => $shipData['firstName'],
                            'last_name' => $shipData['lastName'],
                            'company' => $shipData['company'] ?? null,
                            'address_1' => $shipData['address1'] ?? null,
                            'address_2' => $shipData['address2'] ?? null,
                            'city' => $shipData['city'] ?? null,
                            'state' => $shipData['state'] ?? null,
                            'postcode' => $shipData['postcode'] ?? null,
                            'country' => $shipData['country'] ?? null,
                            'phone' => $shipData['phone'],
                        ]
                    );
                    Shipping::create([
                        'id_order' => $order->id_order,
                        'id_user' => $shippingUser->id_user,
                    ]);
                }

                // 5. Save Recipients
                foreach ($request->recipients as $recipient) {
                    OrderParticipant::create([
                        'id_order' => $order->id_order,
                        'qurban_name' => $recipient['name'],
                        'email' => $recipient['email'],
                        'phone_number' => $recipient['phone'],
                    ]);
                }

                // 6. Save AppLog
                AppLog::create([
                    'data_capture' => json_encode($order),
                    'message' => 'Order created successfully',
                    'status' => 'success',
                    'created_by' => null, // Assuming 'system' or authenticated user
                    'updated_by' => null, // Assuming 'system' or authenticated user
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Order created successfully',
                    'order_id' => $order->id_order,
                    'order_code' => $order->order_code
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order: ' . $e->getMessage()
            ], 500);
        }
    }
}
