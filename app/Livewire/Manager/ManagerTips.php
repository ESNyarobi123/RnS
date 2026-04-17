<?php

namespace App\Livewire\Manager;

use App\Models\Tip;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Tips Dashboard')]
class ManagerTips extends Component
{
    use WithPagination;

    public string $period = 'today';
    public string $workerFilter = 'all';
    public string $search = '';

    public function mount(): void
    {
        $this->authorize('viewAny', Tip::class);
    }

    private function business()
    {
        return Auth::user()->businesses()->firstOrFail();
    }

    public function getTipsProperty()
    {
        $query = Tip::query()
            ->where('business_id', $this->business()->id)
            ->with('worker');

        if ($this->search) {
            $query->where(function ($builder): void {
                $builder->where('customer_name', 'like', '%'.$this->search.'%')
                    ->orWhere('customer_phone', 'like', '%'.$this->search.'%')
                    ->orWhere('source', 'like', '%'.$this->search.'%')
                    ->orWhereHas('worker', fn ($workerQuery) => $workerQuery->where('name', 'like', '%'.$this->search.'%'));
            });
        }

        if ($this->workerFilter !== 'all') {
            $query->where('worker_id', $this->workerFilter);
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

        return $query->orderBy('created_at', 'desc')->paginate(15);
    }

    public function getStatsProperty(): array
    {
        $query = Tip::query()->where('business_id', $this->business()->id);
        $comparisonQuery = Tip::query()->where('business_id', $this->business()->id);

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

    public function getWorkerStatsProperty(): array
    {
        $workers = Tip::query()
            ->where('business_id', $this->business()->id)
            ->when($this->period === 'today', fn ($query) => $query->whereDate('created_at', today()))
            ->when($this->period === 'week', fn ($query) => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]))
            ->when($this->period === 'month', fn ($query) => $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year))
            ->selectRaw('worker_id, COUNT(*) as tip_count, SUM(amount) as total_amount')
            ->groupBy('worker_id')
            ->with('worker')
            ->orderByDesc('total_amount')
            ->limit(5)
            ->get();

        return [
            'top_workers' => $workers,
            'total_workers' => $this->business()->activeWorkerLinks()->count(),
        ];
    }

    public function getWorkersProperty()
    {
        return $this->business()
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
        return view('livewire.manager.manager-tips', [
            'tips' => $this->tips,
            'stats' => $this->stats,
            'workerStats' => $this->workerStats,
            'workers' => $this->workers,
        ]);
    }
}
