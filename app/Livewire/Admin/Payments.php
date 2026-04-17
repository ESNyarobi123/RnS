<?php

namespace App\Livewire\Admin;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('All Payments')]
class Payments extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $method = '';

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

    public function updatedMethod(): void
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
        $this->reset(['search', 'status', 'method', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }

    public function markPaymentCompleted(int $paymentId): void
    {
        $payment = Payment::findOrFail($paymentId);
        $payment->markCompleted();
        unset($this->payments, $this->paymentSummary);
        Flux::toast(__('Payment marked as completed.'));
    }

    public function updatePaymentStatus(int $paymentId, string $newStatus): void
    {
        $payment = Payment::findOrFail($paymentId);
        $statusEnum = PaymentStatus::from($newStatus);
        $payment->update(['status' => $statusEnum]);

        if ($statusEnum === PaymentStatus::Completed && ! $payment->paid_at) {
            $payment->update(['paid_at' => now()]);
        }

        unset($this->payments, $this->paymentSummary);
        Flux::toast(__('Payment status updated to :status.', ['status' => $statusEnum->label()]));
    }

    #[Computed]
    public function payments()
    {
        return Payment::with(['business', 'order'])
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q
                ->where('reference', 'like', "%{$this->search}%")
                ->orWhereHas('business', fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->method, fn ($q) => $q->where('method', $this->method))
            ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->latest()
            ->paginate(20);
    }

    #[Computed]
    public function paymentSummary(): array
    {
        return [
            'total_amount' => (float) Payment::where('status', PaymentStatus::Completed)->sum('amount'),
            'pending_amount' => (float) Payment::where('status', PaymentStatus::Pending)->sum('amount'),
            'completed_count' => Payment::where('status', PaymentStatus::Completed)->count(),
            'pending_count' => Payment::where('status', PaymentStatus::Pending)->count(),
            'failed_count' => Payment::where('status', PaymentStatus::Failed)->count(),
            'today_amount' => (float) Payment::where('status', PaymentStatus::Completed)->whereDate('paid_at', today())->sum('amount'),
        ];
    }

    #[Computed]
    public function statusOptions(): array
    {
        return collect(PaymentStatus::cases())
            ->map(fn ($s) => ['value' => $s->value, 'label' => $s->label()])
            ->all();
    }

    #[Computed]
    public function methodOptions(): array
    {
        return collect(PaymentMethod::cases())
            ->map(fn ($m) => ['value' => $m->value, 'label' => $m->label()])
            ->all();
    }
}
