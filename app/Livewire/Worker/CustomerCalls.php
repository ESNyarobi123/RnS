<?php

namespace App\Livewire\Worker;

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

    public string $search = '';

    public function mount(): void
    {
        $this->authorize('viewAny', WaiterCall::class);
    }

    private function business()
    {
        return Auth::user()->activeBusiness();
    }

    public function getCallsProperty()
    {
        $business = $this->business();

        if (! $business) {
            return collect();
        }

        $query = WaiterCall::query()
            ->where('business_id', $business->id)
            ->with('table');

        if ($this->status !== 'all') {
            $query->where('status', $this->status);
        }

        if ($this->search) {
            $query->where(function ($builder): void {
                $builder->where('customer_name', 'like', '%'.$this->search.'%')
                    ->orWhere('customer_phone', 'like', '%'.$this->search.'%')
                    ->orWhere('notes', 'like', '%'.$this->search.'%')
                    ->orWhereHas('table', fn ($tableQuery) => $tableQuery->where('name', 'like', '%'.$this->search.'%'));
            });
        }

        return $query->latest()->paginate(15);
    }

    public function getStatsProperty(): array
    {
        $business = $this->business();

        if (! $business) {
            return [
                'total' => 0,
                'pending' => 0,
                'responded' => 0,
            ];
        }

        $query = WaiterCall::query()->where('business_id', $business->id);

        return [
            'total' => (clone $query)->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'responded' => (clone $query)->where('status', 'responded')->count(),
        ];
    }

    public function markAsResponded(int $callId): void
    {
        $business = $this->business();

        if (! $business) {
            return;
        }

        $call = WaiterCall::query()
            ->where('business_id', $business->id)
            ->findOrFail($callId);

        $this->authorize('update', $call);
        $call->markResponded();

        Flux::toast(variant: 'success', text: __('Customer call marked as responded.'));
    }

    public function render()
    {
        return view('livewire.worker.customer-calls', [
            'business' => $this->business(),
            'calls' => $this->calls,
            'stats' => $this->stats,
        ]);
    }
}
