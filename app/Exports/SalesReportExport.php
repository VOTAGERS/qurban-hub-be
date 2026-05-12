<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class SalesReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithColumnFormatting
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = Order::with(['user', 'productWoo', 'participants', 'payments'])
            ->whereIn('status', ['A', 'active']);

        if (!empty($this->filters['start_date'])) {
            $query->whereDate('created_at', '>=', $this->filters['start_date']);
        }

        if (!empty($this->filters['end_date'])) {
            $query->whereDate('created_at', '<=', $this->filters['end_date']);
        }

        return $query->orderBy('id_order', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Order Date',
            'Order Code',
            'Customer Name',
            'Customer Email',
            'Customer Phone',
            'Location',
            'Product Name',
            'Qty',
            'Price per Item',
            'Total Price',
            'Payment Method',
            'Payment Status',
            'Qurban Status',
            'Participants List',
            'Total Participants',
        ];
    }

    public function map($order): array
    {
        $paymentMethod = $order->payments->first() ? $order->payments->first()->payment_method : '-';
        $location = $order->user ? trim(($order->user->city ?? '') . ', ' . ($order->user->state ?? '')) : '-';
        
        return [
            $order->created_at->format('Y-m-d H:i'),
            $order->order_code,
            $order->user ? ($order->user->first_name . ' ' . $order->user->last_name) : 'Guest',
            $order->user ? $order->user->email : '-',
            $order->user ? $order->user->phone : '-',
            $location ?: '-',
            $order->productWoo ? $order->productWoo->name : '-',
            $order->quantity,
            $order->productWoo ? $order->productWoo->price : 0,
            $order->total_price,
            strtoupper($paymentMethod),
            ucfirst($order->payment_status),
            ucfirst($order->qurban_status),
            $order->participants->pluck('qurban_name')->implode(', '),
            $order->participants->count(),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true]],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'I' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Price per item
            'J' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Total Price
            'A' => 'yyyy-mm-dd hh:mm', // Order Date
        ];
    }
}
