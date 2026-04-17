<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Services\BusinessPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SelcomWebhookController
{
    public function __invoke(Request $request): JsonResponse
    {
        Log::channel('daily')->info('Selcom webhook received', [
            'payload' => $request->all(),
            'headers' => $request->headers->all(),
        ]);

        $orderId = $request->input('order_id');
        $paymentStatus = $request->input('payment_status');
        $reference = $request->input('reference') ?? $request->input('transid');

        if (! $orderId) {
            return response()->json(['status' => 'ignored', 'reason' => 'no order_id'], 200);
        }

        $payment = app(BusinessPaymentService::class)->handleWebhook($orderId, $paymentStatus, $reference);

        if ($payment) {
            Log::channel('daily')->info('Selcom webhook: payment synced', [
                'payment_id' => $payment->id,
                'provider_order_id' => $orderId,
                'status' => $payment->status->value,
            ]);

            return response()->json(['status' => 'ok'], 200);
        }

        $order = Order::where('order_number', $orderId)->first();

        if (! $order) {
            Log::channel('daily')->warning('Selcom webhook: order not found', ['order_id' => $orderId]);

            return response()->json(['status' => 'not_found'], 200);
        }

        if ($paymentStatus === 'COMPLETED') {
            $payment = $order->payments()
                ->where('status', PaymentStatus::Pending)
                ->first();

            if ($payment) {
                $payment->update([
                    'status' => PaymentStatus::Completed,
                    'reference' => $reference ?? $payment->reference,
                    'paid_at' => now(),
                ]);
            }

            if ($order->status !== OrderStatus::Completed) {
                $order->update([
                    'status' => OrderStatus::Completed,
                    'completed_at' => now(),
                ]);
            }

            Log::channel('daily')->info('Selcom webhook: payment completed', ['order_id' => $orderId]);
        }

        return response()->json(['status' => 'ok'], 200);
    }
}
