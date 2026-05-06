<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Models\Billing;
use App\Models\Shipping;
use App\Models\OrderParticipant;
use App\Models\AppLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutService
{
    public function processCheckout(array $data)
    {
        DB::beginTransaction();
        try {
            // 1. Simpan data user berdasarkan bilings
            $billingUser = $this->findOrCreateUser($data['billing']);

            // 2. Simpan data user berdasarkan shipping
            $shippingUser = $this->findOrCreateUser($data['shipping']);

            // 3. Simpan data order
            $order = Order::create([
                'order_code' => 'ORD-' . Str::upper(Str::random(10)),
                'id_user' => $billingUser->id_user,
                'idproduct_woo' => $data['product_id'],
                'quantity' => $data['quantity'],
                'total_price' => $data['total_price'],
                'payment_status' => 'pending',
                'qurban_status' => 'pending',
                'created_by' => $billingUser->email,
                'updated_by' => 'SYSTEM',
            ]);

            // 4. Simpan id_user dan id_order pada table billing
            Billing::create([
                'id_order' => $order->id_order,
                'id_user' => $billingUser->id_user,
                'created_by' => $billingUser->email,
                'updated_by' => 'SYSTEM',
            ]);

            // 5. Simpan id_user dan id_order pada table shipping
            Shipping::create([
                'id_order' => $order->id_order,
                'id_user' => $shippingUser->id_user,
                'created_by' => $shippingUser->email,
                'updated_by' => 'SYSTEM',
            ]);

            // 6. Simpan data recipients ke table order_participants
            foreach ($data['recipients'] as $recipient) {
                OrderParticipant::create([
                    'id_order' => $order->id_order,
                    'qurban_name' => $recipient['qurban_name'],
                    'email' => $recipient['email'] ?? null,
                    'phone_number' => $recipient['phone_number'] ?? null,
                    'remarks' => $recipient['remarks'] ?? null,
                    'created_by' => $billingUser->email,
                    'updated_by' => 'SYSTEM',
                ]);
            }

            // 7. Simpan data order di table app_logs
            AppLog::create([
                'data_capture' => json_encode($data),
                'message' => 'Checkout processed for order: ' . $order->order_code,
                'created_by' => $billingUser->email,
                'updated_by' => 'SYSTEM',
            ]);

            DB::commit();
            return $order;

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    protected function findOrCreateUser(array $userData)
    {
        // Cari user berdasarkan email atau phone number
        $user = User::where('email', $userData['email'] ?? null)
                    ->orWhere('phone', $userData['phone'] ?? null)
                    ->first();

        if ($user) {
            // Update user jika ada perubahan data (opsional, tergantung kebutuhan)
            $user->update($userData);
        } else {
            // Buat user baru jika tidak ditemukan
            $user = User::create([
                'first_name' => $userData['first_name'],
                'last_name' => $userData['last_name'] ?? null,
                'company' => $userData['company'] ?? null,
                'address_1' => $userData['address_1'] ?? null,
                'address_2' => $userData['address_2'] ?? null,
                'city' => $userData['city'] ?? null,
                'state' => $userData['state'] ?? null,
                'postcode' => $userData['postcode'] ?? null,
                'country' => $userData['country'] ?? null,
                'country_code' => $userData['country_code'] ?? null,
                'email' => $userData['email'] ?? null,
                'phone' => $userData['phone'],
                'status' => 'A',
                'created_by' => $userData['email'] ?? 'system',
                'updated_by' => 'SYSTEM',
            ]);
        }

        // Otomatis assign role eQurban-Customer
        \App\Models\UserRole::updateOrCreate(
            ['id_user' => $user->id_user, 'role_code' => 'eQurban-Customer'],
            [
                'status' => 'active',
                'created_by' => $user->email ?? 'SYSTEM',
                'updated_by' => 'SYSTEM'
            ]
        );

        return $user;
    }
}
