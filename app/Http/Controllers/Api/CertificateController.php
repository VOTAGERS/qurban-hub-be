<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderParticipant;
use App\Models\Certificate;
use App\Services\CertificateGenerator;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CertificateController extends Controller
{
    protected $generator;

    public function __construct(CertificateGenerator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * Display a listing of orders for certificate management
     */
    public function getOrdersForCertificates()
    {
        // Get paid orders with product and user details
        $orders = Order::with(['productWoo', 'user', 'participants.certificate'])
            ->where('payment_status', 'paid')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    /**
     * Get participants for a specific order
     */
    public function getOrderParticipants(Request $request, $orderId)
    {
        $participants = OrderParticipant::with('certificate')
            ->where('id_order', $orderId)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $participants
        ]);
    }

    /**
     * Bulk generate certificates for an order
     */
    public function bulkGenerate(Request $request, $orderId)
    {
        $order = Order::with('user')->findOrFail($orderId);
        $participants = OrderParticipant::where('id_order', $orderId)->get();

        if ($participants->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No participants found for this order.'
            ], 404);
        }

        $results = [];
        $errors = [];

        foreach ($participants as $participant) {
            try {
                $filename = 'certificate_' . $participant->id_participant . '_' . time() . '.pdf';
                $fileUrl = $this->generator->generate($participant->qurban_name, $filename, [
                    'country' => $order->user->country ?? null,
                    'country_code' => $order->user->country_code ?? null,
                ]);

                // Update or Create Certificate record
                Certificate::updateOrCreate(
                    ['id_participant' => $participant->id_participant],
                    [
                        'file_url' => $fileUrl,
                        'generated_at' => Carbon::now(),
                        'is_sent' => false,
                        'status' => 'A',
                        'created_by' => 'system',
                        'updated_by' => 'system'
                    ]
                );

                $results[] = $participant->id_participant;
            } catch (\Exception $e) {
                $errors[] = [
                    'id_participant' => $participant->id_participant,
                    'error' => $e->getMessage()
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => count($results) . ' certificates generated successfully.',
            'generated_ids' => $results,
            'errors' => $errors
        ]);
    }

    /**
     * Download a certificate file
     */
    public function download($certificateId)
    {
        $certificate = Certificate::findOrFail($certificateId);
        
        // Convert URL to absolute path
        $path = str_replace('/storage/', '', $certificate->file_url);
        $fullPath = storage_path('app/public/' . $path);

        if (!file_exists($fullPath)) {
            return response()->json(['success' => false, 'message' => 'File not found.'], 404);
        }

        return response()->download($fullPath);
    }

    /**
     * Publicly generate and download a certificate for a participant
     * This endpoint is intended for public use (email/WA links)
     */
    public function publicDownload($participantId)
    {
        try {
            $participant = OrderParticipant::with(['order.user', 'certificate'])->findOrFail($participantId);
            
            // Basic security: Ensure order is paid
            if ($participant->order->payment_status !== 'paid') {
                return response()->json([
                    'success' => false,
                    'message' => 'Sertifikat belum tersedia. Pesanan Anda belum lunas.'
                ], 403);
            }

            $certificate = $participant->certificate;
            
            // If certificate doesn't exist or file is missing, generate it
            $filePath = $certificate ? str_replace('/storage/', '', $certificate->file_url) : '';
            $fullPath = $filePath ? storage_path('app/public/' . $filePath) : '';

            if (!$certificate || !file_exists($fullPath)) {
                $filename = 'cert_' . $participant->id_participant . '_' . bin2hex(random_bytes(4)) . '.pdf';
                $fileUrl = $this->generator->generate($participant->qurban_name, $filename, [
                    'country' => $participant->order->user->country ?? null,
                    'country_code' => $participant->order->user->country_code ?? null,
                ]);

                $certificate = Certificate::updateOrCreate(
                    ['id_participant' => $participant->id_participant],
                    [
                        'file_url' => $fileUrl,
                        'generated_at' => Carbon::now(),
                        'is_sent' => false,
                        'status' => 'A',
                        'created_by' => 'public_link',
                        'updated_by' => 'public_link'
                    ]
                );

                $filePath = str_replace('/storage/', '', $certificate->file_url);
                $fullPath = storage_path('app/public/' . $filePath);
            }

            if (!file_exists($fullPath)) {
                return response()->json(['success' => false, 'message' => 'Gagal mengunduh sertifikat.'], 500);
            }

            $downloadName = 'Sertifikat_Qurban_' . str_replace(' ', '_', $participant->qurban_name) . '.pdf';
            return response()->download($fullPath, $downloadName);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
