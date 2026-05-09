<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Export Sales Report based on Purchases (Orders) to Excel (CSV format).
     *
     * @param Request $request
     * @return StreamedResponse
     */
    public function exportSalesExcel(Request $request)
    {
        $fileName = 'laporan_penjualan_' . date('Y-m-d_H-i-s') . '.csv';

        $orders = Order::with(['user', 'productWoo'])
            ->whereIn('status', ['A', 'active'])
            ->orderBy('id_order', 'desc')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($orders) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8 Excel support
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header row
            fputcsv($file, [
                'ID Order',
                'Order Code',
                'Customer Name',
                'Customer Email',
                'Product Name',
                'Quantity',
                'Total Price',
                'Payment Status',
                'Qurban Status',
                'Created At'
            ]);

            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->id_order,
                    $order->order_code,
                    $order->user ? ($order->user->first_name . ' ' . $order->user->last_name) : '-',
                    $order->user ? $order->user->email : '-',
                    $order->productWoo ? $order->productWoo->name : '-',
                    $order->quantity,
                    $order->total_price,
                    $order->payment_status,
                    $order->qurban_status,
                    $order->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
