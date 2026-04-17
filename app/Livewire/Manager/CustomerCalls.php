<?php

namespace App\Livewire\Manager;

use App\Models\WaiterCall;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Customer Calls')]
class CustomerCalls extends Component
{
    use WithPagination;

    public string $status = 'all';
    public string $dateRange = 'today';
    public string $search = '';

    public function mount(): void
    {
        $this->authorize('viewAny', WaiterCall::class);
    }

    public function getCustomerCallsProperty()
    {
        $query = WaiterCall::query()
            ->where('business_id', Auth::user()->businesses()->firstOrFail()->id)
            ->with('table');

        if ($this->search) {
            $query->where(function ($builder): void {
                $builder->where('customer_name', 'like', '%'.$this->search.'%')
                    ->orWhere('customer_phone', 'like', '%'.$this->search.'%')
                    ->orWhere('notes', 'like', '%'.$this->search.'%')
                    ->orWhereHas('table', fn ($tableQuery) => $tableQuery->where('name', 'like', '%'.$this->search.'%'));
            });
        }

        if ($this->status !== 'all') {
            $query->where('status', $this->status);
        }

        if ($this->dateRange !== 'all') {
            match ($this->dateRange) {
                'week' => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
                'month' => $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year),
                default => $query->whereDate('created_at', today()),
            };
        }

        return $query->latest()->paginate(15);
    }

    public function getStatsProperty(): array
    {
        $query = WaiterCall::query()->where('business_id', Auth::user()->businesses()->firstOrFail()->id);

        return [
            'total_calls' => (clone $query)->count(),
            'pending_calls' => (clone $query)->where('status', 'pending')->count(),
            'completed_calls' => (clone $query)->where('status', 'responded')->count(),
            'calls_today' => (clone $query)->whereDate('created_at', today())->count(),
            'calls_this_week' => (clone $query)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
        ];
    }

    public function markAsCompleted($callId): void
    {
        $call = WaiterCall::query()
            ->where('business_id', Auth::user()->businesses()->firstOrFail()->id)
            ->findOrFail($callId);

        $this->authorize('update', $call);

        $call->markResponded();

        Flux::toast(variant: 'success', text: __('Customer call marked as responded.'));
    }

    public function render()
    {
        $business = Auth::user()->businesses()->firstOrFail();

        return view('livewire.manager.customer-calls', [
            'customerCalls' => $this->customerCalls,
            'stats' => $this->stats,
            'business' => $business,
        ]);
    }
}
