<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderInvoiceController extends Controller
{
    public function download($orderNumber)
    {
        $order = Order::with('orderItems')->where('order_number', $orderNumber)->firstOrFail();

        // Render view to PDF
        $pdf = Pdf::loadView('invoices.order-pdf', [
            'order' => $order,
        ]);

        // Set paper size to A4
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download("Invoice-{$order->order_number}.pdf");
    }
}