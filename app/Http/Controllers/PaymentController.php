<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\SSLCommerzService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class PaymentController extends Controller
{
    /**
     * Handle SSLCommerz Success Callback.
     */
    public function success(Request $request): RedirectResponse
    {
        $payload = $request->all();
        $tranId = $payload['tran_id'] ?? null;

        $order = $this->resolveOrder($tranId, $payload['order_number'] ?? null);

        if (!$order) {
            return redirect()->route('home')->with('error', 'Invalid order or transaction reference.');
        }

        // Validate payment authenticity
        if (SSLCommerzService::validatePayment($payload)) {
            SSLCommerzService::processSuccess($order, $payload);
            return redirect()->route('order.success', $order->order_number)
                ->with('success', 'Payment completed successfully via ' . ($payload['card_type'] ?? 'SSLCommerz') . '!');
        }

        SSLCommerzService::processFailure($order, $payload, 'failed');
        return redirect()->route('checkout')->with('error', 'Payment validation failed. Please try again.');
    }

    /**
     * Handle SSLCommerz Fail Callback.
     */
    public function fail(Request $request): RedirectResponse
    {
        $payload = $request->all();
        $tranId = $payload['tran_id'] ?? null;

        $order = $this->resolveOrder($tranId, $payload['order_number'] ?? null);

        if ($order) {
            SSLCommerzService::processFailure($order, $payload, 'failed');
        }

        return redirect()->route('checkout')->with('error', 'Your payment transaction was declined or failed. Please retry.');
    }

    /**
     * Handle SSLCommerz Cancel Callback.
     */
    public function cancel(Request $request): RedirectResponse
    {
        $payload = $request->all();
        $tranId = $payload['tran_id'] ?? null;

        $order = $this->resolveOrder($tranId, $payload['order_number'] ?? null);

        if ($order) {
            SSLCommerzService::processFailure($order, $payload, 'cancelled');
        }

        return redirect()->route('checkout')->with('error', 'Payment was cancelled by the user.');
    }

    /**
     * Handle SSLCommerz Instant Payment Notification (IPN) Webhook.
     */
    public function ipn(Request $request): JsonResponse
    {
        $payload = $request->all();
        $tranId = $payload['tran_id'] ?? null;

        $order = $this->resolveOrder($tranId, $payload['order_number'] ?? null);

        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        // Check if already paid
        if ($order->payment_status === 'paid') {
            return response()->json(['status' => 'Order is already marked as paid'], 200);
        }

        $status = strtolower($payload['status'] ?? '');

        if (in_array($status, ['valid', 'validated', 'success']) && SSLCommerzService::validatePayment($payload)) {
            SSLCommerzService::processSuccess($order, $payload);
            return response()->json(['status' => 'IPN Success: Order marked as paid'], 200);
        }

        SSLCommerzService::processFailure($order, $payload, 'failed');
        return response()->json(['status' => 'IPN Processed: Payment marked as failed'], 200);
    }

    /**
     * Interactive Mock Payment Sandbox for Local Development / Testing.
     */
    public function mockSandbox(Request $request, string $orderNumber): View|RedirectResponse
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        $tranId = $request->query('tranId', $order->transaction_id ?: 'SSLCZ_' . $order->order_number . '_' . time());

        return view('payment.mock-sandbox', [
            'order'  => $order,
            'tranId' => $tranId,
        ]);
    }

    /**
     * Resolve order instance from transaction ID or order number.
     */
    protected function resolveOrder(?string $tranId, ?string $orderNumber): ?Order
    {
        if (!empty($tranId)) {
            $order = Order::where('transaction_id', $tranId)->first();
            if ($order) return $order;
        }

        if (!empty($orderNumber)) {
            return Order::where('order_number', $orderNumber)->first();
        }

        return null;
    }
}
