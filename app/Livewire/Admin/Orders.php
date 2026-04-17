<?php

namespace App\Livewire\Admin;

use App\Enums\OrderStatus;
use App\Models\Order;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('All Orders')]
class Orders extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $dateFrom = '';

    #[Url]
    public string $dateTo = '';

    #[Url]
    public string $businessType = '';

    public bool $showDetailModal = false;

    public ?int $selectedOrderId = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    public function updatedBusinessType(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'status', 'dateFrom', 'dateTo', 'businessType']);
        $this->resetPage();
    }

    public function viewOrder(int $orderId): void
    {
        $this->selectedOrderId = $orderId;
        $this->showDetailModal = true;
    }

    public function updateOrderStatus(int $orderId, string $newStatus): void
    {
        $order = Order::findOrFail($orderId);
        $statusEnum = OrderStatus::from($newStatus);

        if ($statusEnum === OrderStatus::Completed) {
            $order->markCompleted();
        } else {
            $order->update(['status' => $statusEnum]);
        }

        unset($this->orders, $this->orderSummary);
        Flux::toast(__('Order status updated to :status.', ['status' => $statusEnum->label()]));
    }

    #[Computed]
    public function selectedOrder(): ?Order
    {
        if (! $this->selectedOrderId) {
            return null;
        }

        return Order::with(['business', 'worker', 'items.product', 'payments'])->find($this->selectedOrderId);
    }

    #[Computed]
    public function orders()
    {
        return Order::with(['business', 'worker', 'items'])
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q
                ->where('order_number', 'like', "%{$this->search}%")
                ->orWhere('customer_name', 'like', "%{$this->search}%")
                ->orWhereHas('business', fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->when($this->businessType, fn ($q) => $q->whereHas('business', fn ($q) => $q->where('type', $this->businessType)))
            ->latest()
            ->paginate(20);
    }

    #[Computed]
    public function statusOptions(): array
    {
        return collect(OrderStatus::cases())
            ->map(fn ($s) => ['value' => $s->value, 'label' => $s->label()])
            ->all();
    }

    #[Computed]
    public function orderSummary(): array
    {
        return [
            'total' => Order::count(),
            'pending' => Order::where('status', OrderStatus::Pending)->count(),
            'in_progress' => Order::where('status', OrderStatus::InProgress)->count(),
            'completed' => Order::where('status', OrderStatus::Completed)->count(),
            'cancelled' => Order::where('status', OrderStatus::Cancelled)->count(),
            'total_value' => (float) Order::where('status', OrderStatus::Completed)->sum('total'),
        ];
    }
}
