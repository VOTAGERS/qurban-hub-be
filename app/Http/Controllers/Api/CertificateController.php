<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderParticipant;
use App\Models\Certificate;
use App\Services\CertificateGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\NumberParseException;

class CertificateController extends Controller
{
    protected $generator;

    public function __construct(CertificateGenerator $generator)
    {
        $this->generator = $generator;
    }
    private function getWAConfig(): array
    {
        return [
            'device_key' => env('WA_DEVICE_KEY'),
            'api_key'    => env('WA_API_KEY'),
            'api_url'    => env('WA_API_URL'),
        ];
    }

    private function buildSignature(string $apiKey): array
    {
        $timestamp = (string) time();
        $signature = hash_hmac('sha256', "{$apiKey}:{$timestamp}", $apiKey);

        return [
            'timestamp' => $timestamp,
            'signature' => $signature,
        ];
    }

    private function formatPhone(string $phone, string $defaultRegion = 'ID'): string
    {
        $phoneUtil = PhoneNumberUtil::getInstance();
        try {
            $parsed = $phoneUtil->parse($phone, $defaultRegion);

            if (!$phoneUtil->isValidNumber($parsed)) {
                Log::warning('Invalid phone number, using fallback', ['phone' => $phone]);
                return $this->formatPhoneFallback($phone);
            }

            return ltrim($phoneUtil->format($parsed, PhoneNumberFormat::E164), '+');
        } catch (NumberParseException $e) {
            Log::warning('Phone parse failed, using fallback', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);
            return $this->formatPhoneFallback($phone);
        }
    }

    private function formatPhoneFallback(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);

        if (str_starts_with($phone, '00')) {
            return substr($phone, 2);
        }

        if (str_starts_with($phone, '0')) {
            return '62' . substr($phone, 1);
        }

        return $phone;
    }

    private function resolveStoragePath(string $fileUrl): string
    {
        $path = str_replace('/storage/', '', $fileUrl);
        return storage_path('app/public/' . $path);
    }

    private function buildWAMessage(OrderParticipant $participant): string
    {
        $downloadLink = url("/api/certificates/public/download/{$participant->id_participant}");

        return "Thank you for your Qurban Sacrifice! 🐄✨\n\n"
            . "Dear *{$participant->qurban_name}*, your Qurban report is now complete.\n\n"
            . "We have successfully delivered your amanah to the rightful recipients. You may now view and download your official certificate via the link below:\n\n"
            . "🔗 {$downloadLink}\n\n"
            . "May this noble act become a continuous charity (*Jariyah*) for you. Thank you for trusting *QurbanHub*. See you next year!\n\n"
            . "Best regards,\n"
            . "*QurbanHub*";
    }

    private function sendWAMessage(OrderParticipant $participant, array $waConfig): array
    {
        $auth    = $this->buildSignature($waConfig['api_key']);
        $phone   = $this->formatPhone(
            $participant->phone_number,
            $participant->country_code ?? 'ID'
        );
        $message = $this->buildWAMessage($participant);
        $cert    = $participant->certificate;

        $httpClient = Http::withHeaders([
            'x-api-key'   => $waConfig['api_key'],
            'x-timestamp' => $auth['timestamp'],
            'x-signature' => $auth['signature'],
        ]);

        try {
            $fullPath = $cert ? $this->resolveStoragePath($cert->file_url) : null;
            $hasFile  = $fullPath && file_exists($fullPath);

            if ($hasFile) {
                $attachName = 'Sertifikat_Qurban_' . str_replace(' ', '_', $participant->qurban_name) . '.pdf';

                $response = $httpClient
                    ->attach('media', file_get_contents($fullPath), $attachName)
                    ->post($waConfig['api_url'], [
                        'deviceKey' => $waConfig['device_key'],
                        'numbers'   => json_encode([$phone]),
                        'message'   => $message,
                    ]);
            } else {
                $response = $httpClient
                    ->asMultipart()
                    ->post($waConfig['api_url'], [
                        ['name' => 'deviceKey', 'contents' => $waConfig['device_key']],
                        ['name' => 'numbers',   'contents' => json_encode([$phone])],
                        ['name' => 'message',   'contents' => $message],
                    ]);
            }

            if ($response->successful() && $response->json('success')) {
                if ($cert) {
                    $cert->update([
                        'is_sent'    => true,
                        'sent_at'    => now(),
                        'status'     => 'sent',
                        'updated_by' => auth()->id() ?? 'system',
                    ]);
                }
                return ['success' => true];
            }

            return [
                'success' => false,
                'reason'  => 'API WA response: ' . ($response->json('message') ?? $response->status()),
            ];
        } catch (\Exception $e) {
            Log::error('WA blast exception', [
                'id_participant' => $participant->id_participant,
                'phone'          => $phone,
                'error'          => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'reason'  => 'Exception: ' . $e->getMessage(),
            ];
        }
    }

    public function getOrdersForCertificates()
    {
        $currentUser = auth('sanctum')->user();
        $isAdmin = $currentUser ? ($currentUser->isSuperAdmin() || $currentUser->isAdmin()) : false;

        // Get paid orders with product and user details
        $query = Order::with(['productWoo', 'user', 'participants.certificate'])
            ->where('payment_status', 'paid')
            ->orderBy('created_at', 'desc');

        // Jika bukan Admin/SuperAdmin, filter hanya pesanan miliknya sendiri
        if (!$isAdmin && $currentUser) {
            $query->where('id_user', $currentUser->id_user);
        }

        $orders = $query->get();

        return response()->json([
            'success' => true,
            'data'    => $orders,
        ]);
    }
    public function getOrderParticipants(Request $request, $orderId)
    {
        $participants = OrderParticipant::with('certificate')
            ->where('id_order', $orderId)
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $participants,
        ]);
    }

    public function bulkGenerate(Request $request, $orderId)
    {
        $order        = Order::with('user')->findOrFail($orderId);
        $participants = OrderParticipant::where('id_order', $orderId)->get();

        if ($participants->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No participants found for this order.',
            ], 404);
        }

        $generated = [];
        $errors    = [];

        foreach ($participants as $participant) {
            try {
                // $filename = 'certificate_' . $participant->id_participant . '_' . time() . '.pdf';
                $safeName = str_replace(' ', '_', $participant->qurban_name);
                $filename = 'Sertifikat_Qurban_' . $safeName . '.pdf';
                $fileUrl  = $this->generator->generate($participant->qurban_name, $filename, [
                    'country'      => $order->user->country      ?? null,
                    'country_code' => $order->user->country_code ?? null,
                ]);
                Certificate::updateOrCreate(
                    ['id_participant' => $participant->id_participant],
                    [
                        'file_url'     => $fileUrl,
                        'generated_at' => Carbon::now(),
                        'is_sent'      => false,
                        'status'       => 'A',
                        'created_by'   => 'system',
                        'updated_by'   => 'system',
                    ]
                );

                $generated[] = $participant->id_participant;
            } catch (\Exception $e) {
                $errors[] = [
                    'id_participant' => $participant->id_participant,
                    'error'          => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'success'       => true,
            'message'       => count($generated) . ' certificates generated successfully.',
            'generated_ids' => $generated,
            'errors'        => $errors,
        ]);
    }


    public function download($certificateId)
    {
        $certificate = Certificate::findOrFail($certificateId);
        $fullPath    = $this->resolveStoragePath($certificate->file_url);

        if (!file_exists($fullPath)) {
            return response()->json([
                'success' => false,
                'message' => 'File not found.',
            ], 404);
        }

        return response()->download($fullPath);
    }

    public function publicDownload($participantId)
    {
        try {
            $participant = OrderParticipant::with(['order.user', 'certificate'])
                ->findOrFail($participantId);

            if ($participant->order->payment_status !== 'paid') {
                return response()->json([
                    'success' => false,
                    'message' => 'Sertifikat belum tersedia. Pesanan Anda belum lunas.',
                ], 403);
            }

            $certificate = $participant->certificate;
            $fullPath    = $certificate ? $this->resolveStoragePath($certificate->file_url) : null;

            if (!$certificate || !$fullPath || !file_exists($fullPath)) {
                $filename = 'cert_' . $participant->id_participant . '_' . bin2hex(random_bytes(4)) . '.pdf';
                $fileUrl  = $this->generator->generate($participant->qurban_name, $filename, [
                    'country'      => $participant->order->user->country      ?? null,
                    'country_code' => $participant->order->user->country_code ?? null,
                ]);

                $certificate = Certificate::updateOrCreate(
                    ['id_participant' => $participant->id_participant],
                    [
                        'file_url'     => $fileUrl,
                        'generated_at' => Carbon::now(),
                        'is_sent'      => false,
                        'status'       => 'A',
                        'created_by'   => 'public_link',
                        'updated_by'   => 'public_link',
                    ]
                );

                $fullPath = $this->resolveStoragePath($certificate->file_url);
            }

            if (!file_exists($fullPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengunduh sertifikat.',
                ], 500);
            }

            $downloadName = 'Sertifikat_Qurban_' . str_replace(' ', '_', $participant->qurban_name) . '.pdf';
            return response()->download($fullPath, $downloadName);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function blastWaOrder(Request $request, $orderId)
    {
        $request->validate([
            'participant_ids'   => 'required|array|min:1',
            'participant_ids.*' => 'integer|exists:order_participants,id_participant',
        ]);

        $waConfig = $this->getWAConfig();

        $participants = OrderParticipant::with('certificate')->where('id_order', $orderId)->whereIn('id_participant', $request->participant_ids)->whereNotNull('phone_number')->get();
        if ($participants->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada peserta valid untuk dikirim.',
            ], 422);
        }
        $success = [];
        $failed  = [];
        foreach ($participants as $participant) {
            if (!$participant->certificate) {
                $failed[] = [
                    'name'   => $participant->qurban_name,
                    'reason' => 'Sertifikat belum di-generate',
                ];
                continue;
            }
            $result = $this->sendWAMessage($participant, $waConfig);
            if ($result['success']) {
                $success[] = $participant->qurban_name;
            } else {
                $failed[] = [
                    'name'   => $participant->qurban_name,
                    'reason' => $result['reason'] ?? 'Gagal kirim',
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => count($success) . ' pesan berhasil dimasukkan ke antrian.',
            'queued'  => count($success),
            'failed'  => $failed,
        ]);
    }
}
