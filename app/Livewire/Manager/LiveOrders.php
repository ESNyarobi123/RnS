<?php

namespace App\Livewire\Manager;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Services\SelcomService;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Live Orders')]
class LiveOrders extends Component
{
    // Create order form
    public bool $showCreateModal = false;

    public string $customerName = '';

    public string $customerPhone = '';

    public string $orderNotes = '';

    /** @var array<int, array{product_id: int, name: string, price: float, quantity: int}> */
    public array $cartItems = [];

    public ?int $selectedCategoryId = null;

    // Payment modal
    public bool $showPaymentModal = false;

    public ?int $payingOrderId = null;

    public string $paymentMethod = 'cash';

    public bool $pushingPayment = false;

    public ?string $paymentStatusMessage = null;

    #[Computed]
    public function business()
    {
        return Auth::user()->businesses()->first();
    }

    #[Computed]
    public function businessType()
    {
        return $this->business?->type;
    }

    #[Computed]
    public function categories()
    {
        return $this->business?->categories()
            ->with(['products' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('sort_order')
            ->get() ?? collect();
    }

    #[Computed]
    public function pendingOrders()
    {
        return $this->business?->orders()
            ->where('status', OrderStatus::Pending)
            ->with('items.product')
            ->latest()
            ->get() ?? collect();
    }

    #[Computed]
    public function preparingOrders()
    {
        return $this->business?->orders()
            ->where('status', OrderStatus::Preparing)
            ->with('items.product')
            ->latest()
            ->get() ?? collect();
    }

    #[Computed]
    public function servedOrders()
    {
        return $this->business?->orders()
            ->where('status', OrderStatus::Served)
            ->with(['items.product', 'payments'])
            ->latest()
            ->get() ?? collect();
    }

    #[Computed]
    public function completedOrders()
    {
        return $this->business?->orders()
            ->where('status', OrderStatus::Completed)
            ->with('items.product')
            ->whereDate('completed_at', today())
            ->latest('completed_at')
            ->limit(20)
            ->get() ?? collect();
    }

    #[Computed]
    public function hasSelcom(): bool
    {
        return SelcomService::forBusiness($this->business) !== null;
    }

    #[Computed]
    public function payingOrder()
    {
        if (! $this->payingOrderId) {
            return null;
        }

        return $this->business?->orders()->with('items.product')->find($this->payingOrderId);
    }

    // === Create Order ===

    public function openCreateOrder(): void
    {
        $this->reset(['customerName', 'customerPhone', 'orderNotes', 'cartItems', 'selectedCategoryId']);
        $this->showCreateModal = true;
    }

    public function addToCart(int $productId): void
    {
        foreach ($this->cartItems as &$item) {
            if ($item['product_id'] === $productId) {
                $item['quantity']++;

                return;
            }
        }

        $product = $this->business->products()->where('is_active', true)->find($productId);
        if ($product) {
            $this->cartItems[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => (float) $product->price,
                'quantity' => 1,
            ];
        }
    }

    public function updateCartQty(int $index, int $qty): void
    {
        if ($qty <= 0) {
            $this->removeFromCart($index);

            return;
        }

        if (isset($this->cartItems[$index])) {
            $this->cartItems[$index]['quantity'] = $qty;
        }
    }

    public function removeFromCart(int $index): void
    {
        unset($this->cartItems[$index]);
        $this->cartItems = array_values($this->cartItems);
    }

    #[Computed]
    public function cartTotal(): float
    {
        return collect($this->cartItems)->sum(fn ($item) => $item['price'] * $item['quantity']);
    }

    public function createOrder(): void
    {
        $this->validate([
            'customerName' => 'required|string|max:255',
            'customerPhone' => 'nullable|string|max:20',
            'cartItems' => 'required|array|min:1',
        ]);

        $order = $this->business->orders()->create([
            'customer_name' => $this->customerName,
            'customer_phone' => $this->customerPhone ?: null,
            'notes' => $this->orderNotes ?: null,
            'status' => OrderStatus::Pending,
            'subtotal' => 0,
            'tax' => 0,
            'total' => 0,
        ]);

        foreach ($this->cartItems as $item) {
            $order->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['price'],
            ]);
        }

        $this->showCreateModal = false;
        $this->resetOrderCaches();

        Flux::toast(variant: 'success', text: __('Order :number created.', ['number' => $order->order_number]));
    }

    // === Status Transitions ===

    public function moveToStatus(int $orderId, string $status): void
    {
        $order = $this->business->orders()->findOrFail($orderId);
        $newStatus = OrderStatus::from($status);

        if ($newStatus === OrderStatus::Completed) {
            $order->update([
                'status' => $newStatus,
                'completed_at' => now(),
            ]);
        } else {
            $order->update(['status' => $newStatus]);
        }

        $this->resetOrderCaches();

        $label = $newStatus->liveLabel($this->businessType);
        Flux::toast(variant: 'success', text: __('Order moved to :status.', ['status' => $label]));
    }

    public function cancelOrder(int $orderId): void
    {
        $order = $this->business->orders()->findOrFail($orderId);
        $order->update(['status' => OrderStatus::Cancelled]);
        $this->resetOrderCaches();

        Flux::toast(variant: 'success', text: __('Order cancelled.'));
    }

    // === Payment ===

    public function openPayment(int $orderId): void
    {
        $this->payingOrderId = $orderId;
        $this->paymentMethod = 'cash';
        $this->pushingPayment = false;
        $this->paymentStatusMessage = null;
        $this->showPaymentModal = true;
    }

    public function processPayment(): void
    {
        $order = $this->business->orders()->findOrFail($this->payingOrderId);

        if ($this->paymentMethod === 'cash') {
            $this->processCashPayment($order);
        } elseif ($this->paymentMethod === 'mobile_money') {
            $this->processMobilePayment($order);
        }
    }

    private function processCashPayment(Order $order): void
    {
        Payment::create([
            'business_id' => $this->business->id,
            'order_id' => $order->id,
            'amount' => $order->total,
            'method' => PaymentMethod::Cash,
            'status' => PaymentStatus::Completed,
            'reference' => 'CASH-'.now()->format('YmdHis'),
            'paid_at' => now(),
        ]);

        $order->update([
            'status' => OrderStatus::Completed,
            'completed_at' => now(),
        ]);

        $this->showPaymentModal = false;
        $this->resetOrderCaches();

        Flux::toast(variant: 'success', text: __('Cash payment recorded. Order completed.'));
    }

    private function processMobilePayment(Order $order): void
    {
        $selcom = SelcomService::forBusiness($this->business);

        if (! $selcom) {
            Flux::toast(variant: 'danger', text: __('Selcom is not configured. Go to Payment Settings to add your API keys.'));

            return;
        }

        if (! $order->customer_phone) {
            Flux::toast(variant: 'danger', text: __('Customer phone number is required for mobile payment.'));

            return;
        }

        $this->pushingPayment = true;
        $this->paymentStatusMessage = __('Creating payment order...');

        $createResult = $selcom->createOrderMinimal([
            'order_id' => $order->order_number,
            'amount' => $order->total,
            'buyer_name' => $order->customer_name,
            'buyer_phone' => $order->customer_phone,
        ]);

        if (! $createResult['success']) {
            $this->pushingPayment = false;
            $this->paymentStatusMessage = null;
            Flux::toast(variant: 'danger', text: __('Payment failed: :error', ['error' => $createResult['error']]));

            return;
        }

        $this->paymentStatusMessage = __('Pushing payment to customer phone...');

        $pushResult = $selcom->walletPayment([
            'order_id' => $order->order_number,
            'msisdn' => $order->customer_phone,
        ]);

        if (! $pushResult['success']) {
            $this->pushingPayment = false;
            $this->paymentStatusMessage = null;
            Flux::toast(variant: 'danger', text: __('Push failed: :error', ['error' => $pushResult['error']]));

            return;
        }

        Payment::create([
            'business_id' => $this->business->id,
            'order_id' => $order->id,
            'amount' => $order->total,
            'method' => PaymentMethod::MobileMoney,
            'status' => PaymentStatus::Pending,
            'reference' => $order->order_number,
        ]);

        $this->pushingPayment = false;
        $this->paymentStatusMessage = __('Payment pushed! Waiting for customer to authorize on their phone.');

        Flux::toast(variant: 'success', text: __('Payment push sent to :phone. Customer will receive a prompt.', ['phone' => $order->customer_phone]));
    }

    public function checkPaymentStatus(int $orderId): void
    {
        $order = $this->business->orders()->findOrFail($orderId);
        $selcom = SelcomService::forBusiness($this->business);

        if (! $selcom) {
            Flux::toast(variant: 'danger', text: __('Selcom not configured.'));

            return;
        }

        $result = $selcom->orderStatus($order->order_number);

        if ($result['success']) {
            $data = $result['data'];
            $paymentStatus = $data['payment_status'] ?? $data[0]['payment_status'] ?? null;

            if ($paymentStatus === 'COMPLETED') {
                $payment = $order->payments()->where('status', PaymentStatus::Pending)->first();
                $payment?->markCompleted();

                $order->update([
                    'status' => OrderStatus::Completed,
                    'completed_at' => now(),
                ]);

                $this->showPaymentModal = false;
                $this->resetOrderCaches();

                Flux::toast(variant: 'success', text: __('Payment confirmed! Order completed.'));
            } else {
                $this->paymentStatusMessage = __('Payment status: :status. Customer may still be authorizing.', ['status' => $paymentStatus ?? 'PENDING']);
            }
        } else {
            Flux::toast(variant: 'danger', text: __('Could not check status: :error', ['error' => $result['error']]));
        }
    }

    public function markPaidManual(int $orderId): void
    {
        $order = $this->business->orders()->findOrFail($orderId);

        $pendingPayment = $order->payments()->where('status', PaymentStatus::Pending)->first();
        if ($pendingPayment) {
            $pendingPayment->markCompleted();
        } else {
            Payment::create([
                'business_id' => $this->business->id,
                'order_id' => $order->id,
                'amount' => $order->total,
                'method' => PaymentMethod::Cash,
                'status' => PaymentStatus::Completed,
                'reference' => 'MANUAL-'.now()->format('YmdHis'),
                'paid_at' => now(),
            ]);
        }

        $order->update([
            'status' => OrderStatus::Completed,
            'completed_at' => now(),
        ]);

        $this->showPaymentModal = false;
        $this->resetOrderCaches();

        Flux::toast(variant: 'success', text: __('Marked as paid. Order completed.'));
    }

    private function resetOrderCaches(): void
    {
        unset($this->pendingOrders, $this->preparingOrders, $this->servedOrders, $this->completedOrders, $this->payingOrder);
    }
}
