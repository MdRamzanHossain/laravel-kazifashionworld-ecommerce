<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\BkashService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BkashController extends Controller
{
    /**
     * Handle bKash Tokenized Return Callback.
     */
    public function callback(Request $request): RedirectResponse
    {
        $paymentID = $request->query('paymentID', $request->input('paymentID'));
        $status = strtolower((string) $request->query('status', $request->input('status', '')));

        if (empty($paymentID)) {
            return redirect()->route('checkout')->with('error', 'bKash payment ID missing.');
        }

        $order = Order::where('transaction_id', $paymentID)
            ->orWhereJsonContains('payment_details->paymentID', $paymentID)
            ->latest('id')
            ->first();

        // Fallback search by order number if passed
        if (!$order && $request->filled('order_number')) {
            $order = Order::where('order_number', $request->input('order_number'))->first();
        }

        if (!$order) {
            return redirect()->route('home')->with('error', 'Order for this bKash transaction was not found.');
        }

        if ($status === 'success') {
            $executeResult = BkashService::executePayment($paymentID);

            if (($executeResult['statusCode'] ?? '') === '0000') {
                BkashService::processSuccess($order, $executeResult);
                $trxId = $executeResult['trxID'] ?? 'Completed';
                return redirect()->route('order.success', $order->order_number)
                    ->with('success', "bKash payment completed successfully! Transaction ID: {$trxId}");
            }

            $errorMessage = $executeResult['statusMessage'] ?? 'bKash payment execution failed.';
            BkashService::processFailure($order, $executeResult, 'failed');
            return redirect()->route('checkout')->with('error', "bKash Error: {$errorMessage}");
        }

        if ($status === 'cancel') {
            BkashService::processFailure($order, $request->all(), 'cancelled');
            return redirect()->route('checkout')->with('error', 'bKash payment was cancelled.');
        }

        BkashService::processFailure($order, $request->all(), 'failed');
        return redirect()->route('checkout')->with('error', 'bKash payment was unsuccessful or declined.');
    }

    /**
     * Interactive bKash Mock Sandbox Simulator for local development / testing.
     */
    public function mockSandbox(Request $request, string $orderNumber): View|RedirectResponse
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        $paymentID = $request->query('paymentID', $order->transaction_id ?: 'MOCK-BKASH-' . $order->order_number . '-' . time());

        return view('payment.bkash-mock', [
            'order'     => $order,
            'paymentID' => $paymentID,
        ]);
    }
}
