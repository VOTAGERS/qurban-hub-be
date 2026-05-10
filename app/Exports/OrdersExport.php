<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrdersExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Order::with(['user', 'productWoo'])
            ->whereIn('status', ['A', 'active'])
            ->orderBy('id_order', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Order Code',
            'Customer Name',
            'Customer Email',
            'Product Name',
            'Quantity',
            'Total Price',
            'Payment Status',
            'Qurban Status',
            'Created At'
        ];
    }

    public function map($order): array
    {
        return [
            $order->order_code,
            $order->user ? ($order->user->first_name . ' ' . $order->user->last_name) : '-',
            $order->user ? $order->user->email : '-',
            $order->productWoo ? $order->productWoo->name : '-',
            $order->quantity,
            $order->total_price,
            $order->payment_status,
            $order->qurban_status,
            $order->created_at ? $order->created_at->format('Y-m-d H:i:s') : '-',
        ];
    }
}
