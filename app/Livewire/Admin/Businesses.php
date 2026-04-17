<?php

namespace App\Livewire\Admin;

use App\Enums\BusinessStatus;
use App\Enums\BusinessType;
use App\Enums\PaymentStatus;
use App\Models\Business;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Manage Businesses')]
class Businesses extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $type = '';

    #[Url]
    public string $status = '';

    public bool $showDetailModal = false;

    public ?int $selectedBusinessId = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedType(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function toggleStatus(int $businessId, string $newStatus): void
    {
        $business = Business::findOrFail($businessId);
        $statusEnum = BusinessStatus::from($newStatus);
        $business->update(['status' => $statusEnum]);
        unset($this->businesses);
        Flux::toast(__('Business status updated to :status.', ['status' => $statusEnum->label()]));
    }

    public function viewBusiness(int $businessId): void
    {
        $this->selectedBusinessId = $businessId;
        $this->showDetailModal = true;
    }

    #[Computed]
    public function selectedBusiness(): ?Business
    {
        if (! $this->selectedBusinessId) {
            return null;
        }

        return Business::with([
            'owner',
            'activeWorkerLinks.worker',
            'categories' => fn ($q) => $q->orderBy('sort_order'),
            'orders' => fn ($q) => $q->with('worker')->latest()->limit(5),
        ])
            ->withCount(['orders', 'products', 'categories', 'activeWorkerLinks', 'stocks', 'feedbacks'])
            ->withSum(['payments as total_revenue' => fn ($q) => $q->where('status', PaymentStatus::Completed)], 'amount')
            ->withAvg('feedbacks', 'rating')
            ->find($this->selectedBusinessId);
    }

    #[Computed]
    public function businesses()
    {
        return Business::query()
            ->with(['owner', 'activeWorkerLinks'])
            ->withCount(['orders', 'products', 'categories'])
            ->withSum(['payments as total_revenue' => fn ($q) => $q->where('status', PaymentStatus::Completed)], 'amount')
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$this->search}%")
                ->orWhereHas('owner', fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ))
            ->when($this->type, fn ($q) => $q->where('type', $this->type))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->latest()
            ->paginate(15);
    }

    #[Computed]
    public function typeOptions(): array
    {
        return collect(BusinessType::cases())
            ->map(fn ($t) => ['value' => $t->value, 'label' => $t->label()])
            ->all();
    }

    #[Computed]
    public function statusOptions(): array
    {
        return collect(BusinessStatus::cases())
            ->map(fn ($s) => ['value' => $s->value, 'label' => $s->label()])
            ->all();
    }
}
