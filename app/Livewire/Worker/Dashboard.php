<?php

namespace App\Livewire\Worker;

use App\Enums\OrderStatus;
use App\Enums\PayrollStatus;
use App\Models\Tip;
use App\Models\WaiterCall;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Worker Dashboard')]
class Dashboard extends Component
{
    #[Computed]
    public function worker()
    {
        return Auth::user();
    }

    #[Computed]
    public function isLinked(): bool
    {
        return $this->worker->isLinkedToBusiness();
    }

    #[Computed]
    public function activeLink()
    {
        return $this->worker->activeBusinessLink();
    }

    #[Computed]
    public function business()
    {
        return $this->worker->activeBusiness();
    }

    #[Computed]
    public function title(): string
    {
        return $this->worker->workerTitle();
    }

    #[Computed]
    public function stats(): array
    {
        if (! $this->isLinked) {
            return [];
        }

        $business = $this->business;
        $ordersQuery = $this->worker->assignedOrders()->where('business_id', $business->id);

        return [
            'orders_today' => (clone $ordersQuery)->whereDate('created_at', today())->count(),
            'orders_pending' => (clone $ordersQuery)->where('status', OrderStatus::Pending)->count(),
            'orders_completed' => (clone $ordersQuery)->where('status', OrderStatus::Completed)->whereDate('created_at', today())->count(),
            'orders_in_progress' => (clone $ordersQuery)->where('status', OrderStatus::InProgress)->count(),
            'total_orders' => (clone $ordersQuery)->count(),
            'total_completed' => (clone $ordersQuery)->where('status', OrderStatus::Completed)->count(),
            'revenue_today' => (float) (clone $ordersQuery)->where('status', OrderStatus::Completed)->whereDate('created_at', today())->sum('total'),
            'total_revenue' => (float) (clone $ordersQuery)->where('status', OrderStatus::Completed)->sum('total'),
        ];
    }

    #[Computed]
    public function recentOrders()
    {
        if (! $this->isLinked) {
            return collect();
        }

        return $this->worker->assignedOrders()
            ->where('business_id', $this->business->id)
            ->with('items.product')
            ->latest()
            ->take(5)
            ->get();
    }

    #[Computed]
    public function payrollStats(): array
    {
        if (! $this->isLinked) {
            return [];
        }

        return [
            'total_earned' => (float) $this->worker->payrolls()
                ->where('business_id', $this->business->id)
                ->where('status', PayrollStatus::Paid)
                ->sum('amount'),
            'pending_payroll' => (float) $this->worker->payrolls()
                ->where('business_id', $this->business->id)
                ->where('status', PayrollStatus::Pending)
                ->sum('amount'),
        ];
    }

    #[Computed]
    public function feedbackStats(): array
    {
        if (! $this->isLinked) {
            return [];
        }

        $feedbacks = $this->worker->feedbacks()->where('business_id', $this->business->id);

        return [
            'total_reviews' => (clone $feedbacks)->count(),
            'avg_rating' => (float) (clone $feedbacks)->avg('rating'),
        ];
    }

    #[Computed]
    public function tipsSummary(): array
    {
        if (! $this->isLinked) {
            return [];
        }

        $query = Tip::query()
            ->where('business_id', $this->business->id)
            ->where('worker_id', $this->worker->id);

        return [
            'today' => (float) (clone $query)->whereDate('created_at', today())->sum('amount'),
            'total' => (float) (clone $query)->sum('amount'),
        ];
    }

    #[Computed]
    public function customersServed(): int
    {
        if (! $this->isLinked) {
            return 0;
        }

        return $this->worker->assignedOrders()
            ->where('business_id', $this->business->id)
            ->whereNotNull('customer_phone')
            ->distinct('customer_phone')
            ->count('customer_phone');
    }

    #[Computed]
    public function pendingCalls(): int
    {
        if (! $this->isLinked) {
            return 0;
        }

        return WaiterCall::query()
            ->where('business_id', $this->business->id)
            ->where('status', 'pending')
            ->count();
    }
}
