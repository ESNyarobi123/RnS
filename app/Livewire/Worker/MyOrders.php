<?php

namespace App\Livewire\Worker;

use App\Enums\OrderStatus;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('My Orders')]
class MyOrders extends Component
{
    use WithPagination;

    #[Url]
    public string $status = '';

    #[Url]
    public string $search = '';

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
    public function statusCounts(): array
    {
        if (! $this->business) {
            return [];
        }

        $base = $this->worker->assignedOrders()->where('business_id', $this->business->id);

        return [
            'all' => (clone $base)->count(),
            'pending' => (clone $base)->where('status', OrderStatus::Pending)->count(),
            'in_progress' => (clone $base)->whereIn('status', [OrderStatus::InProgress, OrderStatus::Preparing])->count(),
            'completed' => (clone $base)->where('status', OrderStatus::Completed)->count(),
            'cancelled' => (clone $base)->where('status', OrderStatus::Cancelled)->count(),
        ];
    }

    #[Computed]
    public function orders()
    {
        if (! $this->business) {
            return collect();
        }

        $query = $this->worker->assignedOrders()
            ->where('business_id', $this->business->id)
            ->with(['items.product', 'payments']);

        if ($this->status) {
            if ($this->status === 'in_progress') {
                $query->whereIn('status', [OrderStatus::InProgress, OrderStatus::Preparing]);
            } else {
                $query->where('status', $this->status);
            }
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('order_number', 'like', "%{$this->search}%")
                    ->orWhere('customer_name', 'like', "%{$this->search}%");
            });
        }

        return $query->latest()->paginate(15);
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }
}
