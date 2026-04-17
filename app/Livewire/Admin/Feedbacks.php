<?php

namespace App\Livewire\Admin;

use App\Models\Feedback;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Feedback & Reviews')]
class Feedbacks extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $rating = '';

    #[Url]
    public string $businessType = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedRating(): void
    {
        $this->resetPage();
    }

    public function updatedBusinessType(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'rating', 'businessType']);
        $this->resetPage();
    }

    public function deleteFeedback(int $feedbackId): void
    {
        Feedback::findOrFail($feedbackId)->delete();
        unset($this->feedbacks, $this->feedbackSummary);
        Flux::toast(__('Feedback deleted.'));
    }

    #[Computed]
    public function feedbacks()
    {
        return Feedback::with(['business', 'worker', 'order'])
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q
                ->where('customer_name', 'like', "%{$this->search}%")
                ->orWhere('comment', 'like', "%{$this->search}%")
                ->orWhereHas('business', fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ))
            ->when($this->rating, fn ($q) => $q->where('rating', $this->rating))
            ->when($this->businessType, fn ($q) => $q->whereHas('business', fn ($q) => $q->where('type', $this->businessType)))
            ->latest()
            ->paginate(20);
    }

    #[Computed]
    public function feedbackSummary(): array
    {
        return [
            'total' => Feedback::count(),
            'avg_rating' => (float) Feedback::avg('rating'),
            'five_star' => Feedback::where('rating', 5)->count(),
            'four_star' => Feedback::where('rating', 4)->count(),
            'three_star' => Feedback::where('rating', 3)->count(),
            'two_star' => Feedback::where('rating', 2)->count(),
            'one_star' => Feedback::where('rating', 1)->count(),
        ];
    }
}
