<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Business;
use App\Models\BusinessWorker;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Tip;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BusinessPaymentService
{
    /**
     * @return array{success: bool, payment: ?Payment, error: ?string}
     */
    public function initiateOrderMobileMoney(Order $order, float $amount, string $customerPhone, ?string $customerName = null): array
    {
        $order->loadMissing('business');
        $business = $order->business;
        $selcom = SelcomService::forBusiness($business);

        if (! $selcom) {
            return [
                'success' => false,
                'payment' => null,
                'error' => 'Selcom is not configured for this business.',
            ];
        }

        $remainingAmount = max(0.0, (float) $order->total - (float) $order->payments()->where('status', PaymentStatus::Completed)->sum('amount'));

        if ($remainingAmount <= 0) {
            return [
                'success' => false,
                'payment' => null,
                'error' => 'This order has already been fully paid.',
            ];
        }

        if ($amount > $remainingAmount) {
            return [
                'success' => false,
                'payment' => null,
                'error' => 'The payment amount cannot exceed the remaining order balance.',
            ];
        }

        $providerOrderId = $this->generateProviderOrderId('PAY', $business->id);

        $createResult = $selcom->createOrderMinimal([
            'order_id' => $providerOrderId,
            'amount' => $amount,
            'buyer_name' => $customerName ?: ($order->customer_name ?: 'WhatsApp Customer'),
            'buyer_phone' => $customerPhone,
            'remarks' => 'WhatsApp payment for '.$order->order_number,
        ]);

        if (! $createResult['success']) {
            return [
                'success' => false,
                'payment' => null,
                'error' => $createResult['error'] ?? 'Could not create Selcom order.',
            ];
        }

        $pushResult = $selcom->walletPayment([
            'order_id' => $providerOrderId,
            'msisdn' => $customerPhone,
        ]);

        if (! $pushResult['success']) {
            return [
                'success' => false,
                'payment' => null,
                'error' => $pushResult['error'] ?? 'Could not push payment to customer phone.',
            ];
        }

        $payment = Payment::query()->create([
            'business_id' => $business->id,
            'order_id' => $order->id,
            'amount' => $amount,
            'method' => PaymentMethod::MobileMoney,
            'status' => PaymentStatus::Pending,
            'reference' => $order->order_number,
            'provider' => 'selcom',
            'provider_order_id' => $providerOrderId,
            'customer_phone' => $customerPhone,
            'customer_name' => $customerName ?: $order->customer_name,
            'metadata' => [
                'purpose' => 'order',
                'order_number' => $order->order_number,
                'business_id' => $business->id,
            ],
        ]);

        return [
            'success' => true,
            'payment' => $payment,
            'error' => null,
        ];
    }

    /**
     * @return array{success: bool, payment: ?Payment, error: ?string}
     */
    public function initiateTipMobileMoney(Business $business, ?BusinessWorker $workerLink, float $amount, string $customerPhone, ?string $customerName = null): array
    {
        $selcom = SelcomService::forBusiness($business);

        if (! $selcom) {
            return [
                'success' => false,
                'payment' => null,
                'error' => 'Selcom is not configured for this business.',
            ];
        }

        $providerOrderId = $this->generateProviderOrderId('TIP', $business->id);
        $workerName = $workerLink?->worker?->name;

        $createResult = $selcom->createOrderMinimal([
            'order_id' => $providerOrderId,
            'amount' => $amount,
            'buyer_name' => $customerName ?: 'WhatsApp Customer',
            'buyer_phone' => $customerPhone,
            'remarks' => $workerName
                ? 'WhatsApp tip for '.$workerName
                : 'WhatsApp tip payment',
        ]);

        if (! $createResult['success']) {
            return [
                'success' => false,
                'payment' => null,
                'error' => $createResult['error'] ?? 'Could not create Selcom tip order.',
            ];
        }

        $pushResult = $selcom->walletPayment([
            'order_id' => $providerOrderId,
            'msisdn' => $customerPhone,
        ]);

        if (! $pushResult['success']) {
            return [
                'success' => false,
                'payment' => null,
                'error' => $pushResult['error'] ?? 'Could not push tip payment to customer phone.',
            ];
        }

        $payment = Payment::query()->create([
            'business_id' => $business->id,
            'order_id' => null,
            'amount' => $amount,
            'method' => PaymentMethod::MobileMoney,
            'status' => PaymentStatus::Pending,
            'reference' => $providerOrderId,
            'provider' => 'selcom',
            'provider_order_id' => $providerOrderId,
            'customer_phone' => $customerPhone,
            'customer_name' => $customerName,
            'metadata' => [
                'purpose' => 'tip',
                'business_id' => $business->id,
                'worker_link_id' => $workerLink?->id,
                'worker_user_id' => $workerLink?->worker_id,
                'source' => 'whatsapp',
            ],
        ]);

        return [
            'success' => true,
            'payment' => $payment,
            'error' => null,
        ];
    }

    /**
     * @return array{success: bool, payment: Payment, provider_status: string|null, error: ?string}
     */
    public function syncPaymentStatus(Payment $payment): array
    {
        if ($payment->provider !== 'selcom' || $payment->status !== PaymentStatus::Pending) {
            return [
                'success' => true,
                'payment' => $payment->fresh() ?? $payment,
                'provider_status' => null,
                'error' => null,
            ];
        }

        $payment->loadMissing('business', 'order', 'tip');
        $selcom = SelcomService::forBusiness($payment->business);

        if (! $selcom) {
            return [
                'success' => false,
                'payment' => $payment,
                'provider_status' => null,
                'error' => 'Selcom is not configured for this business.',
            ];
        }

        $providerOrderId = $payment->provider_order_id ?: $payment->reference;
        $result = $selcom->orderStatus((string) $providerOrderId);

        if (! $result['success']) {
            return [
                'success' => false,
                'payment' => $payment,
                'provider_status' => null,
                'error' => $result['error'] ?? 'Could not check payment status.',
            ];
        }

        $data = $result['data'];
        $providerStatus = $data['payment_status'] ?? $data[0]['payment_status'] ?? null;
        $reference = $data['reference'] ?? $data[0]['reference'] ?? $data['transid'] ?? $data[0]['transid'] ?? null;

        if ($providerStatus === 'COMPLETED') {
            $payment = $this->completePayment($payment, $reference);
        }

        if (in_array($providerStatus, ['FAILED', 'CANCELLED', 'DECLINED'], true)) {
            $payment = $this->failPayment($payment, $reference);
        }

        return [
            'success' => true,
            'payment' => $payment->fresh() ?? $payment,
            'provider_status' => $providerStatus,
            'error' => null,
        ];
    }

    public function handleWebhook(?string $providerOrderId, ?string $providerStatus, ?string $reference = null): ?Payment
    {
        if (! $providerOrderId) {
            return null;
        }

        $payment = Payment::query()
            ->where(function ($query) use ($providerOrderId): void {
                $query->where('provider_order_id', $providerOrderId)
                    ->orWhere('reference', $providerOrderId);
            })
            ->latest('id')
            ->first();

        if (! $payment && str_starts_with($providerOrderId, 'ORD-')) {
            $payment = Payment::query()
                ->whereHas('order', fn ($query) => $query->where('order_number', $providerOrderId))
                ->where('status', PaymentStatus::Pending)
                ->latest('id')
                ->first();
        }

        if (! $payment) {
            return null;
        }

        if ($providerStatus === 'COMPLETED') {
            return $this->completePayment($payment, $reference)->fresh();
        }

        if (in_array($providerStatus, ['FAILED', 'CANCELLED', 'DECLINED'], true)) {
            return $this->failPayment($payment, $reference)->fresh();
        }

        return $payment;
    }

    private function completePayment(Payment $payment, ?string $reference = null): Payment
    {
        return DB::transaction(function () use ($payment, $reference): Payment {
            $payment->refresh();

            if ($payment->status !== PaymentStatus::Completed) {
                $payment->update([
                    'status' => PaymentStatus::Completed,
                    'reference' => $reference ?? $payment->reference,
                    'paid_at' => now(),
                ]);
            }

            $payment->loadMissing('order', 'tip');
            $purpose = data_get($payment->metadata, 'purpose');

            if ($purpose === 'tip' && ! $payment->tip) {
                $this->createTipFromPayment($payment);
            }

            if ($payment->order) {
                $this->syncOrderSettlement($payment->order->fresh());
            }

            return $payment->fresh() ?? $payment;
        });
    }

    private function failPayment(Payment $payment, ?string $reference = null): Payment
    {
        $payment->update([
            'status' => PaymentStatus::Failed,
            'reference' => $reference ?? $payment->reference,
        ]);

        return $payment->fresh() ?? $payment;
    }

    private function createTipFromPayment(Payment $payment): Tip
    {
        $workerId = data_get($payment->metadata, 'worker_user_id');

        return Tip::query()->create([
            'business_id' => $payment->business_id,
            'worker_id' => $workerId,
            'payment_id' => $payment->id,
            'amount' => $payment->amount,
            'customer_phone' => $payment->customer_phone,
            'customer_name' => $payment->customer_name,
            'source' => data_get($payment->metadata, 'source', 'whatsapp'),
        ]);
    }

    private function syncOrderSettlement(?Order $order): void
    {
        if (! $order) {
            return;
        }

        $paidAmount = (float) $order->payments()->where('status', PaymentStatus::Completed)->sum('amount');

        if ($paidAmount >= (float) $order->total && $order->status !== OrderStatus::Completed) {
            $order->update([
                'status' => OrderStatus::Completed,
                'completed_at' => now(),
            ]);
        }
    }

    private function generateProviderOrderId(string $prefix, int $businessId): string
    {
        return strtoupper($prefix.'-'.$businessId.'-'.now()->format('YmdHis').'-'.Str::random(6));
    }
}
