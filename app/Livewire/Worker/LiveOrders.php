<?php

namespace App\Livewire\Worker;

use App\Enums\OrderStatus;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Live Orders')]
class LiveOrders extends Component
{
    public ?int $selectedOrderId = null;

    public bool $showOrderDetail = false;

    #[Computed]
    public function worker()
    {
        return Auth::user();
    }

    #[Computed]
    public function business()
    {
        return $this->worker->activeBusiness();
    }

    #[Computed]
    public function businessType()
    {
        return $this->business?->type;
    }

    #[Computed]
    public function pendingOrders()
    {
        return $this->ordersForStatus(OrderStatus::Pending);
    }

    #[Computed]
    public function preparingOrders()
    {
        return $this->ordersForStatus(OrderStatus::Preparing);
    }

    #[Computed]
    public function servedOrders()
    {
        return $this->ordersForStatus(OrderStatus::Served);
    }

    #[Computed]
    public function completedOrders()
    {
        if (! $this->business) {
            return collect();
        }

        return $this->worker->assignedOrders()
            ->where('business_id', $this->business->id)
            ->where('status', OrderStatus::Completed)
            ->whereDate('completed_at', today())
            ->with('items.product')
            ->latest('completed_at')
            ->limit(20)
            ->get();
    }

    #[Computed]
    public function todayStats(): array
    {
        if (! $this->business) {
            return ['total' => 0, 'completed' => 0, 'revenue' => 0];
        }

        $base = $this->worker->assignedOrders()
            ->where('business_id', $this->business->id)
            ->whereDate('created_at', today());

        return [
            'total' => (clone $base)->count(),
            'completed' => (clone $base)->where('status', OrderStatus::Completed)->count(),
            'revenue' => (float) (clone $base)->where('status', OrderStatus::Completed)->sum('total'),
        ];
    }

    #[Computed]
    public function selectedOrder()
    {
        if (! $this->selectedOrderId || ! $this->business) {
            return null;
        }

        return $this->worker->assignedOrders()
            ->where('business_id', $this->business->id)
            ->with(['items.product', 'payments'])
            ->find($this->selectedOrderId);
    }

    public function viewOrder(int $orderId): void
    {
        $this->selectedOrderId = $orderId;
        $this->showOrderDetail = true;
    }

    private function ordersForStatus(OrderStatus $status)
    {
        if (! $this->business) {
            return collect();
        }

        return $this->worker->assignedOrders()
            ->where('business_id', $this->business->id)
            ->where('status', $status)
            ->with('items.product')
            ->latest()
            ->get();
    }
}
