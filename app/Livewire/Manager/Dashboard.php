<?php

namespace App\Livewire\Manager;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\PayrollStatus;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Manager Dashboard')]
class Dashboard extends Component
{
    #[Computed]
    public function business()
    {
        return Auth::user()->businesses()->with(['activeWorkerLinks.worker'])->first();
    }

    #[Computed]
    public function stats(): array
    {
        $business = $this->business;

        if (! $business) {
            return [];
        }

        $ordersQuery = $business->orders();

        return [
            'workers' => $business->activeWorkerLinks()->count(),
            'categories' => $business->categories()->count(),
            'products' => $business->activeProducts()->count(),
            'orders_today' => (clone $ordersQuery)->whereDate('created_at', today())->count(),
            'orders_pending' => (clone $ordersQuery)->where('status', OrderStatus::Pending)->count(),
            'orders_in_progress' => (clone $ordersQuery)->where('status', OrderStatus::InProgress)->count(),
            'orders_completed_today' => (clone $ordersQuery)->where('status', OrderStatus::Completed)->whereDate('created_at', today())->count(),
            'total_orders' => (clone $ordersQuery)->count(),
            'total_completed' => (clone $ordersQuery)->where('status', OrderStatus::Completed)->count(),
            'revenue_today' => (float) (clone $ordersQuery)->where('status', OrderStatus::Completed)->whereDate('created_at', today())->sum('total'),
            'total_revenue' => (float) (clone $ordersQuery)->where('status', OrderStatus::Completed)->sum('total'),
            'total_payroll_paid' => (float) $business->payrolls()->where('status', PayrollStatus::Paid)->sum('amount'),
            'pending_payroll' => (float) $business->payrolls()->where('status', PayrollStatus::Pending)->sum('amount'),
        ];
    }

    #[Computed]
    public function hasBusiness(): bool
    {
        return Auth::user()->businesses()->exists();
    }

    #[Computed]
    public function businessType()
    {
        return $this->business?->type;
    }

    #[Computed]
    public function recentOrders()
    {
        if (! $this->hasBusiness) {
            return collect();
        }

        return $this->business->orders()
            ->with(['worker', 'items.product'])
            ->latest()
            ->take(5)
            ->get();
    }

    #[Computed]
    public function feedbackStats(): array
    {
        if (! $this->hasBusiness) {
            return [];
        }

        $feedbacks = $this->business->feedbacks();

        return [
            'total_reviews' => (clone $feedbacks)->count(),
            'avg_rating' => (float) (clone $feedbacks)->avg('rating'),
        ];
    }
}
