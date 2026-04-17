<?php

namespace App\Livewire\Worker;

use App\Models\Tip;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('My Tips')]
class MyTips extends Component
{
    use WithPagination;

    public string $period = 'today';
    public string $search = '';

    public function mount(): void
    {
        $this->authorize('viewAny', Tip::class);
    }

    private function worker()
    {
        return Auth::user();
    }

    private function business()
    {
        return $this->worker()->activeBusiness();
    }

    public function getTipsProperty()
    {
        $business = $this->business();

        if (! $business) {
            return collect();
        }

        $query = $this->worker()->tips()->where('business_id', $business->id);

        if ($this->search) {
            $query->where(function ($builder): void {
                $builder->where('customer_name', 'like', '%'.$this->search.'%')
                    ->orWhere('customer_phone', 'like', '%'.$this->search.'%')
                    ->orWhere('source', 'like', '%'.$this->search.'%');
            });
        }

        switch ($this->period) {
            case 'today':
                $query->whereDate('created_at', today());
                break;
            case 'week':
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('created_at', now()->month)
                      ->whereYear('created_at', now()->year);
                break;
            case 'all':
                // No filtering
                break;
        }

        return $query->orderBy('created_at', 'desc')->paginate(10);
    }

    public function getStatsProperty(): array
    {
        $business = $this->business();

        if (! $business) {
            return [
                'total' => 0,
                'count' => 0,
                'average' => 0,
                'percentage_change' => 0,
                'comparison_total' => 0,
            ];
        }

        $query = $this->worker()->tips()->where('business_id', $business->id);
        $comparisonQuery = $this->worker()->tips()->where('business_id', $business->id);

        switch ($this->period) {
            case 'today':
                $query->whereDate('created_at', today());
                $comparisonQuery->whereDate('created_at', today()->subDay());
                break;
            case 'week':
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                $comparisonQuery->whereBetween('created_at', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('created_at', now()->month)
                      ->whereYear('created_at', now()->year);
                $comparisonQuery->whereMonth('created_at', now()->subMonth()->month)
                    ->whereYear('created_at', now()->subMonth()->year);
                break;
            case 'all':
                $comparisonQuery = null;
                break;
        }

        $tips = $query->get();
        $total = $tips->sum('amount');
        $count = $tips->count();
        $average = $count > 0 ? $total / $count : 0;

        $comparisonTotal = $comparisonQuery?->sum('amount') ?? 0;
        $percentageChange = $comparisonTotal > 0 ? (($total - $comparisonTotal) / $comparisonTotal) * 100 : 0;

        return [
            'total' => $total,
            'count' => $count,
            'average' => $average,
            'percentage_change' => $percentageChange,
            'comparison_total' => $comparisonTotal,
        ];
    }

    public function render()
    {
        return view('livewire.worker.my-tips', [
            'tips' => $this->tips,
            'stats' => $this->stats,
        ]);
    }
}
