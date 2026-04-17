<?php

namespace App\Livewire\Manager;

use App\Enums\PayrollStatus;
use App\Models\Payroll;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Payrolls')]
class ManagePayrolls extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    // Create payroll form
    public bool $showCreateModal = false;

    public ?int $payrollWorkerId = null;

    public string $payrollAmount = '';

    public string $payrollPeriodStart = '';

    public string $payrollPeriodEnd = '';

    public string $payrollNotes = '';

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
    public function payrolls()
    {
        return $this->business->payrolls()
            ->with('worker')
            ->when($this->search, fn ($q) => $q->whereHas('worker', fn ($q) => $q
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('global_number', 'like', "%{$this->search}%")
            ))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->latest()
            ->paginate(15);
    }

    #[Computed]
    public function payrollSummary(): array
    {
        $query = $this->business->payrolls();

        return [
            'total_paid' => (float) (clone $query)->where('status', PayrollStatus::Paid)->sum('amount'),
            'total_pending' => (float) (clone $query)->where('status', PayrollStatus::Pending)->sum('amount'),
            'paid_count' => (clone $query)->where('status', PayrollStatus::Paid)->count(),
            'pending_count' => (clone $query)->where('status', PayrollStatus::Pending)->count(),
        ];
    }

    #[Computed]
    public function statusOptions(): array
    {
        return collect(PayrollStatus::cases())
            ->map(fn ($s) => ['value' => $s->value, 'label' => $s->label()])
            ->all();
    }

    #[Computed]
    public function activeWorkers()
    {
        return $this->business->activeWorkerLinks()->with('worker')->get()->pluck('worker');
    }

    public function openCreatePayroll(): void
    {
        $this->reset(['payrollWorkerId', 'payrollAmount', 'payrollPeriodStart', 'payrollPeriodEnd', 'payrollNotes']);
        $this->showCreateModal = true;
    }

    public function savePayroll(): void
    {
        $this->validate([
            'payrollWorkerId' => 'required|exists:users,id',
            'payrollAmount' => 'required|numeric|min:1',
            'payrollPeriodStart' => 'required|date',
            'payrollPeriodEnd' => 'required|date|after_or_equal:payrollPeriodStart',
            'payrollNotes' => 'nullable|string|max:1000',
        ]);

        $this->business->payrolls()->create([
            'worker_id' => $this->payrollWorkerId,
            'amount' => $this->payrollAmount,
            'period_start' => $this->payrollPeriodStart,
            'period_end' => $this->payrollPeriodEnd,
            'notes' => $this->payrollNotes ?: null,
            'status' => PayrollStatus::Pending,
        ]);

        $this->showCreateModal = false;
        unset($this->payrolls, $this->payrollSummary);

        Flux::toast(variant: 'success', text: __('Payroll created.'));
    }

    public function markPaid(int $payrollId): void
    {
        $payroll = $this->business->payrolls()->findOrFail($payrollId);
        $payroll->markPaid();

        unset($this->payrolls, $this->payrollSummary);

        Flux::toast(variant: 'success', text: __('Payroll marked as paid.'));
    }
}
