<?php

namespace App\Livewire\Admin;

use App\Enums\BusinessStatus;
use App\Enums\BusinessType;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\PayrollStatus;
use App\Enums\StockStatus;
use App\Enums\UserRole;
use App\Models\Business;
use App\Models\BusinessWorker;
use App\Models\Feedback;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Payroll;
use App\Models\Stock;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Admin Dashboard')]
class Dashboard extends Component
{
    #[Computed]
    public function platformStats(): array
    {
        return [
            'total_businesses' => Business::count(),
            'active_businesses' => Business::where('status', BusinessStatus::Active)->count(),
            'restaurants' => Business::where('type', BusinessType::Restaurant)->count(),
            'salons' => Business::where('type', BusinessType::Salon)->count(),
            'total_managers' => User::where('role', UserRole::Manager)->count(),
            'total_workers' => User::where('role', UserRole::Worker)->count(),
            'active_links' => BusinessWorker::where('is_active', true)->count(),
            'total_orders' => Order::count(),
            'orders_today' => Order::whereDate('created_at', today())->count(),
            'pending_orders' => Order::where('status', OrderStatus::Pending)->count(),
            'completed_orders' => Order::where('status', OrderStatus::Completed)->count(),
            'total_revenue' => (float) Payment::where('status', PaymentStatus::Completed)->sum('amount'),
            'revenue_today' => (float) Payment::where('status', PaymentStatus::Completed)->whereDate('paid_at', today())->sum('amount'),
            'revenue_this_month' => (float) Payment::where('status', PaymentStatus::Completed)->whereMonth('paid_at', now()->month)->whereYear('paid_at', now()->year)->sum('amount'),
            'pending_payments' => Payment::where('status', PaymentStatus::Pending)->count(),
            'total_payrolls' => Payroll::count(),
            'pending_payrolls' => Payroll::where('status', PayrollStatus::Pending)->count(),
            'paid_payrolls_amount' => (float) Payroll::where('status', PayrollStatus::Paid)->sum('amount'),
            'total_feedback' => Feedback::count(),
            'avg_rating' => (float) Feedback::avg('rating'),
            'low_stock_items' => Stock::where('status', StockStatus::LowStock)->count(),
            'out_of_stock_items' => Stock::where('status', StockStatus::OutOfStock)->count(),
        ];
    }

    #[Computed]
    public function recentOrders()
    {
        return Order::with(['business', 'worker'])
            ->latest()
            ->limit(10)
            ->get();
    }

    #[Computed]
    public function recentPayments()
    {
        return Payment::with(['business'])
            ->latest()
            ->limit(10)
            ->get();
    }

    #[Computed]
    public function topBusinesses()
    {
        return Business::withCount(['orders', 'activeWorkerLinks'])
            ->withSum(['payments as total_revenue' => fn ($q) => $q->where('status', PaymentStatus::Completed)], 'amount')
            ->where('status', BusinessStatus::Active)
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function recentManagers()
    {
        return User::where('role', UserRole::Manager)
            ->withCount('businesses')
            ->latest()
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function recentWorkers()
    {
        return User::where('role', UserRole::Worker)
            ->with(['businessLinks' => fn ($q) => $q->where('is_active', true)->with('business')])
            ->latest()
            ->limit(5)
            ->get();
    }
}
