<?php

namespace App\Livewire\Admin;

use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Manage Managers')]
class Managers extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $hasBusiness = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedHasBusiness(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function managers()
    {
        return User::where('role', UserRole::Manager)
            ->with(['businesses' => fn ($q) => $q->withCount(['orders', 'activeWorkerLinks', 'products'])->withSum(['payments as total_revenue' => fn ($q) => $q->where('status', PaymentStatus::Completed)], 'amount')])
            ->withCount('businesses')
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%")
            ))
            ->when($this->hasBusiness === 'yes', fn ($q) => $q->has('businesses'))
            ->when($this->hasBusiness === 'no', fn ($q) => $q->doesntHave('businesses'))
            ->latest()
            ->paginate(15);
    }
}
