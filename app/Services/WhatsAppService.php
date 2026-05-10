<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Send WhatsApp message using a third-party API (e.g., Fonnte)
     *
     * @param string $to Recipient phone number
     * @param string $message Message text
     * @return bool
     */
    public static function sendMessage($to, $message)
    {
        $apiUrl = env('WHATSAPP_API_URL', 'https://api.fonnte.com/send');
        $apiKey = env('WHATSAPP_API_KEY');

        if (empty($apiKey)) {
            Log::warning("WhatsApp API Key is not set in .env");
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $apiKey,
            ])->post($apiUrl, [
                'target' => $to,
                'message' => $message,
                'delay' => '2',
                'countryCode' => '62', // Default to Indonesia
            ]);

            if ($response->successful()) {
                Log::info("WhatsApp message sent to {$to}");
                return true;
            } else {
                Log::error("Failed to send WhatsApp message to {$to}: " . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Error sending WhatsApp message: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send order notifications (Processing or Completed)
     *
     * @param \App\Models\Order $order
     * @return void
     */
    public static function sendOrderNotification($order)
    {
        $phone = $order->user->phone ?? null;
        if (!$phone) return;

        // Clean phone number
        $phone = preg_replace('/\D/', '', $phone);
        if (strpos($phone, '0') === 0) {
            $phone = '62' . substr($phone, 1);
        }

        $customerName = ($order->user->first_name ?? '') . ' ' . ($order->user->last_name ?? '');
        $productName = $order->productWoo->name ?? 'Produk Qurban';
        $totalPayment = number_format($order->total_price, 0, ',', '.');
        $invoice = $order->order_code;

        // Template Pesanan Diproses (Pending)
        if ($order->payment_status === 'pending') {
            $message = "===================\nPESANAN DIPROSES\n===================\n\n" .
                "Jika {$customerName} sudah transfer, kirim bukti screenshotnya kesini yah. 😊\n\n" .
                "Nomor Invoice : {$invoice}\n" .
                "Produk : {$productName} X{$order->quantity}\n" .
                "Biaya Transaksi : Rp. 0\n" .
                "PPN : 0,00% (Rp. 0)\n" .
                "Total Pembayaran : Rp. {$totalPayment}\n\n\n" .
                "Support Installasi\n" .
                "Link Login Temporary : -\n\n" .
                "Bank BCA, nomor rekening 0540856940.\n" .
                "Atas nama BADRUDIN.\n\n" .
                "Salam,\n" .
                "Badrudin (Owner)";

            self::sendMessage($phone, $message);
        }

        // Template Pesanan Selesai (Paid)
        if ($order->payment_status === 'paid') {
            $message = "================\nPESANAN SELESAI\n================\n\n" .
                "Terima kasih, pesanan {$customerName} sudah selesai.\n\n" .
                "Nomor Invoice : {$invoice}\n" .
                "Produk : {$productName} X{$order->quantity}\n" .
                "Biaya Transaksi : Rp. 0\n" .
                "PPN : 0,00% (Rp. 0)\n" .
                "Total Pembayaran : Rp. {$totalPayment}\n\n\n" .
                "Support Installasi\n" .
                "Link Login Temporary : -\n\n" .
                "Masa aktif hingga\n\n" .
                now()->addYear()->format('d M Y') . "\n\n" .
                "Salam,\n" .
                "QurbanHub (System)";

            self::sendMessage($phone, $message);
        }
    }
}
