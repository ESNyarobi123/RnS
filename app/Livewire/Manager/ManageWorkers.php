<?php

namespace App\Livewire\Manager;

use App\Actions\Business\LinkWorker;
use App\Actions\Business\UnlinkWorker;
use App\Enums\LinkType;
use App\Enums\UserRole;
use App\Models\User;
use App\Services\QrCodeService;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Manage Workers')]
class ManageWorkers extends Component
{
    #[Validate('required|string|regex:/^TIP-\d{6}$/')]
    public string $global_number = '';

    public string $link_type = 'permanent';

    public ?string $expires_at = null;

    public bool $showLinkModal = false;

    // Worker Profile
    public bool $showProfileModal = false;

    public ?int $profileWorkerId = null;

    // TIP Search
    public string $searchTip = '';

    public bool $showSearchResult = false;

    public ?array $searchResultData = null;

    #[Computed]
    public function business()
    {
        return Auth::user()->businesses()->first();
    }

    #[Computed]
    public function activeLinks()
    {
        return $this->business
            ?->activeWorkerLinks()
            ->with('worker')
            ->get() ?? collect();
    }

    #[Computed]
    public function pastLinks()
    {
        return $this->business
            ?->workerLinks()
            ->where('is_active', false)
            ->with('worker')
            ->latest('unlinked_at')
            ->limit(10)
            ->get() ?? collect();
    }

    #[Computed]
    public function workerTitle(): string
    {
        return $this->business?->type->workerTitle() ?? 'Worker';
    }

    #[Computed]
    public function workerTitlePlural(): string
    {
        return $this->business?->type->workerTitlePlural() ?? 'Workers';
    }

    #[Computed]
    public function profileWorker()
    {
        if (! $this->profileWorkerId) {
            return null;
        }

        return User::where('id', $this->profileWorkerId)
            ->where('role', UserRole::Worker)
            ->first();
    }

    #[Computed]
    public function profileStats(): array
    {
        $worker = $this->profileWorker;

        if (! $worker || ! $this->business) {
            return [];
        }

        $ordersCount = $worker->assignedOrders()
            ->where('business_id', $this->business->id)
            ->count();

        $totalEarnings = $worker->payrolls()
            ->where('business_id', $this->business->id)
            ->where('status', 'paid')
            ->sum('amount');

        $feedbackCount = $worker->feedbacks()
            ->where('business_id', $this->business->id)
            ->count();

        $avgRating = $worker->feedbacks()
            ->where('business_id', $this->business->id)
            ->avg('rating');

        $link = $this->business->workerLinks()
            ->where('worker_id', $worker->id)
            ->where('is_active', true)
            ->first();

        return [
            'orders_count' => $ordersCount,
            'total_earnings' => $totalEarnings,
            'feedback_count' => $feedbackCount,
            'avg_rating' => $avgRating ? round($avgRating, 1) : null,
            'linked_at' => $link?->linked_at,
            'link_type' => $link?->link_type,
            'expires_at' => $link?->expires_at,
        ];
    }

    public function openLinkModal(): void
    {
        $this->reset(['global_number', 'link_type', 'expires_at']);
        $this->showLinkModal = true;
    }

    public function viewProfile(int $workerId): void
    {
        $this->profileWorkerId = $workerId;
        unset($this->profileWorker, $this->profileStats);
        $this->showProfileModal = true;
    }

    public function searchWorker(): void
    {
        $tip = strtoupper(trim($this->searchTip));

        if (! preg_match('/^TIP-\d{6}$/', $tip)) {
            Flux::toast(variant: 'danger', text: __('Invalid TIP format. Use TIP-XXXXXX (e.g. TIP-123456).'));

            return;
        }

        $worker = User::where('global_number', $tip)
            ->where('role', UserRole::Worker)
            ->first();

        if (! $worker) {
            $this->searchResultData = null;
            $this->showSearchResult = true;
            Flux::toast(variant: 'danger', text: __('No worker found with number :tip.', ['tip' => $tip]));

            return;
        }

        $isLinkedHere = $this->business?->workerLinks()
            ->where('worker_id', $worker->id)
            ->where('is_active', true)
            ->exists();

        $isLinkedElsewhere = $worker->businessLinks()
            ->where('is_active', true)
            ->when($this->business, fn ($q) => $q->where('business_id', '!=', $this->business->id))
            ->exists();

        $totalOrders = $worker->assignedOrders()->count();
        $totalFeedbacks = $worker->feedbacks()->count();
        $avgRating = $worker->feedbacks()->avg('rating');

        $this->searchResultData = [
            'id' => $worker->id,
            'name' => $worker->name,
            'email' => $worker->email,
            'phone' => $worker->phone,
            'global_number' => $worker->global_number,
            'avatar' => $worker->hasImage() ? $worker->imageUrl() : null,
            'initials' => $worker->initials(),
            'is_linked_here' => $isLinkedHere,
            'is_linked_elsewhere' => $isLinkedElsewhere,
            'total_orders' => $totalOrders,
            'total_feedbacks' => $totalFeedbacks,
            'avg_rating' => $avgRating ? round($avgRating, 1) : null,
            'joined' => $worker->created_at->format('M d, Y'),
        ];

        $this->showSearchResult = true;
    }

    public function linkFromSearch(): void
    {
        if (! $this->searchResultData) {
            return;
        }

        $this->global_number = $this->searchResultData['global_number'];
        $this->showSearchResult = false;
        $this->searchResultData = null;
        $this->searchTip = '';
        $this->showLinkModal = true;
    }

    public function clearSearch(): void
    {
        $this->searchTip = '';
        $this->showSearchResult = false;
        $this->searchResultData = null;
    }

    public function linkWorker(LinkWorker $action, QrCodeService $qrCodeService): void
    {
        $this->validate();

        $link = $action->execute($this->business, [
            'global_number' => $this->global_number,
            'link_type' => $this->link_type,
            'expires_at' => $this->link_type === LinkType::Temporary->value ? $this->expires_at : null,
        ]);

        $qrCodeService->generateWorkerQrCode($link->fresh(['worker']));

        $this->showLinkModal = false;
        $this->reset(['global_number', 'link_type', 'expires_at']);

        unset($this->activeLinks, $this->pastLinks);

        Flux::toast(variant: 'success', text: __(':title linked successfully.', ['title' => $this->workerTitle]));
    }

    public function regenerateQrCode(int $linkId, QrCodeService $qrCodeService): void
    {
        $link = $this->business
            ?->workerLinks()
            ->whereKey($linkId)
            ->with('worker')
            ->firstOrFail();

        $qrCodeService->generateWorkerQrCode($link);

        unset($this->activeLinks, $this->pastLinks);

        Flux::toast(variant: 'success', text: __('Worker QR regenerated successfully.'));
    }

    public function unlinkWorker(UnlinkWorker $action, int $workerId): void
    {
        $action->execute($this->business, $workerId);

        unset($this->activeLinks, $this->pastLinks);

        Flux::toast(variant: 'success', text: __(':title unlinked.', ['title' => $this->workerTitle]));
    }
}
