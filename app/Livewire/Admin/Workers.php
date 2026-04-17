<?php

namespace App\Livewire\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Manage Workers')]
class Workers extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $linkStatus = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedLinkStatus(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function workers()
    {
        return User::where('role', UserRole::Worker)
            ->with(['businessLinks' => fn ($q) => $q->with('business')->latest()])
            ->withCount(['businessLinks as active_links_count' => fn ($q) => $q->where('is_active', true)])
            ->withCount('assignedOrders')
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%")
                ->orWhere('global_number', 'like', "%{$this->search}%")
            ))
            ->when($this->linkStatus === 'linked', fn ($q) => $q->whereHas('businessLinks', fn ($q) => $q->where('is_active', true)))
            ->when($this->linkStatus === 'unlinked', fn ($q) => $q->whereDoesntHave('businessLinks', fn ($q) => $q->where('is_active', true)))
            ->latest()
            ->paginate(20);
    }
}
