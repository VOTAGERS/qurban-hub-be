<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Export Sales Report based on Purchases (Orders) to Excel (.xlsx format).
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportSalesExcel(Request $request)
    {
        $fileName = 'sales-report-' . date('Y-m-d') . '.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\OrdersExport, $fileName);
    }
}
