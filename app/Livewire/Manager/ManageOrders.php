<?php

namespace App\Livewire\Manager;

use App\Enums\OrderStatus;
use App\Models\Order;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Orders')]
class ManageOrders extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

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

    public function resetFilters(): void
    {
        $this->reset(['search', 'status']);
        $this->resetPage();
    }

    #[Computed]
    public function business()
    {
        return Auth::user()->businesses()->first();
    }

    #[Computed]
    public function orders()
    {
        return $this->business->orders()
            ->with(['worker', 'items.product'])
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q
                ->where('order_number', 'like', "%{$this->search}%")
                ->orWhere('customer_name', 'like', "%{$this->search}%")
                ->orWhereHas('worker', fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->latest()
            ->paginate(15);
    }

    #[Computed]
    public function orderSummary(): array
    {
        $query = $this->business->orders();

        return [
            'total' => (clone $query)->count(),
            'pending' => (clone $query)->where('status', OrderStatus::Pending)->count(),
            'in_progress' => (clone $query)->where('status', OrderStatus::InProgress)->count(),
            'completed_today' => (clone $query)->where('status', OrderStatus::Completed)->whereDate('created_at', today())->count(),
            'revenue_today' => (float) (clone $query)->where('status', OrderStatus::Completed)->whereDate('created_at', today())->sum('total'),
        ];
    }

    #[Computed]
    public function statusOptions(): array
    {
        return collect(OrderStatus::cases())
            ->map(fn ($s) => ['value' => $s->value, 'label' => $s->label()])
            ->all();
    }

    public function viewOrder(int $orderId): void
    {
        $this->selectedOrderId = $orderId;
        $this->showDetailModal = true;
    }

    #[Computed]
    public function selectedOrder(): ?Order
    {
        if (! $this->selectedOrderId) {
            return null;
        }

        return $this->business->orders()
            ->with(['worker', 'items.product', 'payments'])
            ->find($this->selectedOrderId);
    }

    public function updateOrderStatus(int $orderId, string $newStatus): void
    {
        $order = $this->business->orders()->findOrFail($orderId);
        $statusEnum = OrderStatus::from($newStatus);

        if ($statusEnum === OrderStatus::Completed) {
            $order->markCompleted();
        } else {
            $order->update(['status' => $statusEnum]);
        }

        unset($this->orders, $this->orderSummary);
        Flux::toast(variant: 'success', text: __('Order :status.', ['status' => $statusEnum->label()]));
    }
}
