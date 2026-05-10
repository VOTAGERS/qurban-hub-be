<?php

namespace App\Http\Controllers\Api;

use App\Exports\SalesReportExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function exportSalesExcel(Request $request)
    {
        $fileName = 'laporan_penjualan_'.date('Y-m-d_H-i-s').'.xlsx';

        return Excel::download(new SalesReportExport($request->all()), $fileName);
    }
}
