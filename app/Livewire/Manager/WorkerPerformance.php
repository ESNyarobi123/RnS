<?php

namespace App\Livewire\Manager;

use App\Models\BusinessWorker;
use App\Models\Order;
use App\Models\Tip;
use App\Models\Feedback;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Worker Performance')]
class WorkerPerformance extends Component
{
    use WithPagination;

    public string $period = 'today';
    public string $search = '';
    public string $sortBy = 'performance_score';
    public string $sortDirection = 'desc';

    public function mount(): void
    {
        $this->authorize('viewAny', BusinessWorker::class);
    }

    public function getWorkersProperty()
    {
        $business = auth()->user()->businesses()->first();
        
        $query = BusinessWorker::where('business_id', $business->id)
                              ->where('is_active', true);

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        // Calculate performance metrics
        $workers = $query->get()->map(function ($worker) {
            $worker->performance_score = $this->calculatePerformanceScore($worker);
            $worker->tips_total = $this->getWorkerTipsTotal($worker);
            $worker->orders_total = $this->getWorkerOrdersTotal($worker);
            $worker->rating_average = $this->getWorkerRatingAverage($worker);
            $worker->customers_served = $this->getWorkerCustomersServed($worker);
            $worker->response_time_avg = $this->getWorkerResponseTime($worker);
            return $worker;
        });

        // Apply sorting
        if ($this->sortBy === 'performance_score') {
            $workers = $this->sortDirection === 'desc' 
                ? $workers->sortByDesc('performance_score')
                : $workers->sortBy('performance_score');
        } elseif ($this->sortBy === 'tips_total') {
            $workers = $this->sortDirection === 'desc'
                ? $workers->sortByDesc('tips_total')
                : $workers->sortBy('tips_total');
        } elseif ($this->sortBy === 'orders_total') {
            $workers = $this->sortDirection === 'desc'
                ? $workers->sortByDesc('orders_total')
                : $workers->sortBy('orders_total');
        } elseif ($this->sortBy === 'rating_average') {
            $workers = $this->sortDirection === 'desc'
                ? $workers->sortByDesc('rating_average')
                : $workers->sortBy('rating_average');
        }

        return $this->paginateCollection($workers, 15);
    }

    public function getTopPerformersProperty()
    {
        $business = auth()->user()->businesses()->first();
        
        $workers = BusinessWorker::where('business_id', $business->id)
                              ->where('is_active', true)
                              ->get()
                              ->map(function ($worker) {
                                  $worker->performance_score = $this->calculatePerformanceScore($worker);
                                  $worker->tips_total = $this->getWorkerTipsTotal($worker);
                                  $worker->orders_total = $this->getWorkerOrdersTotal($worker);
                                  $worker->rating_average = $this->getWorkerRatingAverage($worker);
                                  return $worker;
                              })
                              ->sortByDesc('performance_score')
                              ->take(5);

        return $workers;
    }

    public function getPerformanceStatsProperty(): array
    {
        $business = auth()->user()->businesses()->first();
        $workers = BusinessWorker::where('business_id', $business->id)
                              ->where('is_active', true)
                              ->get();

        $totalWorkers = $workers->count();
        $avgPerformance = $workers->avg(function ($worker) {
            return $this->calculatePerformanceScore($worker);
        });

        $topPerformer = $workers->max(function ($worker) {
            return $this->calculatePerformanceScore($worker);
        });

        return [
            'total_workers' => $totalWorkers,
            'average_performance' => round($avgPerformance, 2),
            'top_performer_score' => $topPerformer ? $this->calculatePerformanceScore($topPerformer) : 0,
            'top_performer_name' => $topPerformer ? $topPerformer->name : null,
        ];
    }

    private function calculatePerformanceScore(BusinessWorker $worker): float
    {
        $tipsScore = $this->getWorkerTipsTotal($worker) * 0.3;
        $ordersScore = $this->getWorkerOrdersTotal($worker) * 0.4;
        $ratingScore = $this->getWorkerRatingAverage($worker) * 20;
        $responseScore = max(0, (300 - $this->getWorkerResponseTime($worker)) / 60) * 0.3;

        return $tipsScore + $ordersScore + $ratingScore + $responseScore;
    }

    private function getWorkerTipsTotal(BusinessWorker $worker): float
    {
        $query = $worker->receivedTips();

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
        }

        return $query->sum('amount') ?: 0;
    }

    private function getWorkerOrdersTotal(BusinessWorker $worker): int
    {
        $query = $worker->orders();

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
        }

        return $query->count();
    }

    private function getWorkerRatingAverage(BusinessWorker $worker): float
    {
        $query = $worker->feedbacks();

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
        }

        return $query->avg('rating') ?: 0;
    }

    private function getWorkerCustomersServed(BusinessWorker $worker): int
    {
        $query = $worker->orders()->with('customer');

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
        }

        return $query->get()->unique('customer.id')->count();
    }

    private function getWorkerResponseTime(BusinessWorker $worker): int
    {
        // Placeholder - you'd calculate this from actual response time data
        // This could come from customer call logs or order assignment times
        return rand(60, 300); // seconds
    }

    private function paginateCollection($collection, $perPage)
    {
        $currentPage = request()->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        
        $itemsForCurrentPage = $collection->slice($offset, $perPage)->values();
        
        return new \Illuminate\Pagination\LengthAwarePaginator(
            $itemsForCurrentPage,
            $collection->count(),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ]
        );
    }

    public function render()
    {
        $business = auth()->user()->businesses()->first();
        $businessType = $business->type->value;
        $workerTitle = $businessType === 'salon' ? __('Stylists') : __('Waiters');
        
        return view('livewire.manager.worker-performance', [
            'workers' => $this->workers,
            'topPerformers' => $this->topPerformers,
            'performanceStats' => $this->performanceStats,
            'businessType' => $businessType,
            'workerTitle' => $workerTitle,
        ]);
    }
}
