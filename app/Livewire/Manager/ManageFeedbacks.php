<?php

namespace App\Livewire\Manager;

use App\Models\Feedback;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Feedback Management')]
class ManageFeedbacks extends Component
{
    use WithPagination;

    public string $search = '';
    public string $rating = 'all';
    public string $workerFilter = 'all';
    public string $dateRange = '30';

    public function mount(): void
    {
        $this->authorize('viewAny', Feedback::class);
    }

    public function delete(Feedback $feedback): void
    {
        $this->authorize('delete', $feedback);

        $feedback->delete();
        Flux::toast(variant: 'success', text: __('Feedback deleted successfully.'));
    }

    public function getFeedbacksProperty()
    {
        $business = Auth::user()->businesses()->firstOrFail();

        $query = Feedback::query()
            ->where('business_id', $business->id)
            ->with(['worker', 'order']);

        if ($this->search) {
            $query->where(function ($builder): void {
                $builder->where('comment', 'like', '%'.$this->search.'%')
                    ->orWhere('customer_name', 'like', '%'.$this->search.'%')
                    ->orWhereHas('worker', fn ($workerQuery) => $workerQuery->where('name', 'like', '%'.$this->search.'%'))
                    ->orWhereHas('order', fn ($orderQuery) => $orderQuery->where('order_number', 'like', '%'.$this->search.'%'));
            });
        }

        if ($this->rating !== 'all') {
            $query->where('rating', $this->rating);
        }

        if ($this->workerFilter !== 'all') {
            $query->where('worker_id', $this->workerFilter);
        }

        if ($this->dateRange !== 'all') {
            $query->where('created_at', '>=', now()->subDays($this->dateRange));
        }

        return $query->orderBy('created_at', 'desc')->paginate(15);
    }

    public function getStatsProperty(): array
    {
        $business = Auth::user()->businesses()->firstOrFail();

        $query = Feedback::query()->where('business_id', $business->id);

        if ($this->dateRange !== 'all') {
            $query->where('created_at', '>=', now()->subDays($this->dateRange));
        }

        $feedbacks = $query->get();
        $total = $feedbacks->count();
        $averageRating = $feedbacks->avg('rating') ?: 0;
        
        $ratingCounts = $feedbacks->groupBy('rating')->map->count();
        $ratingBreakdown = [
            '5' => $ratingCounts->get(5, 0),
            '4' => $ratingCounts->get(4, 0),
            '3' => $ratingCounts->get(3, 0),
            '2' => $ratingCounts->get(2, 0),
            '1' => $ratingCounts->get(1, 0),
        ];

        // Calculate NPS (Net Promoter Score)
        $promoters = $ratingCounts->get(5, 0) + $ratingCounts->get(4, 0);
        $detractors = $ratingCounts->get(1, 0) + $ratingCounts->get(2, 0);
        $passives = $ratingCounts->get(3, 0);
        $respondents = $total;
        
        $npsScore = $respondents > 0 ? (($promoters - $detractors) / $respondents) * 100 : 0;

        return [
            'total' => $total,
            'average_rating' => round($averageRating, 2),
            'rating_breakdown' => $ratingBreakdown,
            'nps_score' => round($npsScore, 1),
            'promoters' => $promoters,
            'detractors' => $detractors,
            'passives' => $passives,
        ];
    }

    public function getWorkersProperty()
    {
        return Auth::user()->businesses()->firstOrFail()
            ->activeWorkerLinks()
            ->with('worker:id,name')
            ->get()
            ->map(fn ($link) => $link->worker)
            ->unique('id')
            ->sortBy('name')
            ->values();
    }

    public function render()
    {
        return view('livewire.manager.manage-feedbacks', [
            'feedbacks' => $this->feedbacks,
            'stats' => $this->stats,
            'workers' => $this->workers,
        ]);
    }
}
