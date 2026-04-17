<?php

namespace App\Livewire\Worker;

use App\Enums\PayrollStatus;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('My Payrolls')]
class MyPayrolls extends Component
{
    use WithPagination;

    #[Url]
    public string $status = '';

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
    public function summary(): array
    {
        if (! $this->business) {
            return [];
        }

        $base = $this->worker->payrolls()->where('business_id', $this->business->id);

        return [
            'total_earned' => (float) (clone $base)->where('status', PayrollStatus::Paid)->sum('amount'),
            'pending' => (float) (clone $base)->where('status', PayrollStatus::Pending)->sum('amount'),
            'paid_count' => (clone $base)->where('status', PayrollStatus::Paid)->count(),
            'pending_count' => (clone $base)->where('status', PayrollStatus::Pending)->count(),
        ];
    }

    #[Computed]
    public function payrolls()
    {
        if (! $this->business) {
            return collect();
        }

        $query = $this->worker->payrolls()
            ->where('business_id', $this->business->id);

        if ($this->status) {
            $query->where('status', $this->status);
        }

        return $query->latest()->paginate(15);
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }
}
