<?php

namespace App\Livewire\Admin;

use App\Enums\PayrollStatus;
use App\Models\Payroll;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('All Payrolls')]
class Payrolls extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $dateFrom = '';

    #[Url]
    public string $dateTo = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'status', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }

    public function markPayrollPaid(int $payrollId): void
    {
        $payroll = Payroll::findOrFail($payrollId);
        $payroll->markPaid();
        unset($this->payrolls, $this->payrollSummary);
        Flux::toast(__('Payroll marked as paid.'));
    }

    #[Computed]
    public function payrolls()
    {
        return Payroll::with(['business', 'worker'])
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q
                ->whereHas('worker', fn ($q) => $q->where('name', 'like', "%{$this->search}%")->orWhere('global_number', 'like', "%{$this->search}%"))
                ->orWhereHas('business', fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->dateFrom, fn ($q) => $q->where('period_start', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->where('period_end', '<=', $this->dateTo))
            ->latest()
            ->paginate(20);
    }

    #[Computed]
    public function payrollSummary(): array
    {
        return [
            'total_paid' => (float) Payroll::where('status', PayrollStatus::Paid)->sum('amount'),
            'total_pending' => (float) Payroll::where('status', PayrollStatus::Pending)->sum('amount'),
            'paid_count' => Payroll::where('status', PayrollStatus::Paid)->count(),
            'pending_count' => Payroll::where('status', PayrollStatus::Pending)->count(),
        ];
    }

    #[Computed]
    public function statusOptions(): array
    {
        return collect(PayrollStatus::cases())
            ->map(fn ($s) => ['value' => $s->value, 'label' => $s->label()])
            ->all();
    }
}
