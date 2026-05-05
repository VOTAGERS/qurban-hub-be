<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderParticipant;
use App\Models\Certificate;
use App\Services\CertificateGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $orders = Order::with(['productWoo', 'user'])
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
    public function getOrderParticipants($orderId)
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
    public function bulkGenerate($orderId)
    {
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
                $fileUrl = $this->generator->generate($participant->qurban_name, $filename);

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
}
