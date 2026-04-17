<?php

namespace App\Livewire\Worker;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('My Reviews')]
class MyReviews extends Component
{
    use WithPagination;

    #[Url]
    public string $rating = '';

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

        $base = $this->worker->feedbacks()->where('business_id', $this->business->id);

        $total = (clone $base)->count();
        $avg = (float) (clone $base)->avg('rating');

        $breakdown = [];
        for ($i = 5; $i >= 1; $i--) {
            $count = (clone $base)->where('rating', $i)->count();
            $breakdown[$i] = [
                'count' => $count,
                'pct' => $total > 0 ? round(($count / $total) * 100) : 0,
            ];
        }

        return [
            'total' => $total,
            'avg' => $avg ? round($avg, 1) : 0,
            'breakdown' => $breakdown,
        ];
    }

    #[Computed]
    public function reviews()
    {
        if (! $this->business) {
            return collect();
        }

        $query = $this->worker->feedbacks()
            ->where('business_id', $this->business->id)
            ->with('order');

        if ($this->rating) {
            $query->where('rating', (int) $this->rating);
        }

        return $query->latest()->paginate(15);
    }

    public function updatedRating(): void
    {
        $this->resetPage();
    }
}
