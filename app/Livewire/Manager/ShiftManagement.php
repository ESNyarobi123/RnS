<?php

namespace App\Livewire\Manager;

use App\Models\BusinessWorker;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Shift Management')]
class ShiftManagement extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = 'all';
    public string $dateFilter = 'today';

    public ?BusinessWorker $editingWorker = null;
    public string $shiftStart = '';
    public string $shiftEnd = '';
    public string $shiftType = 'regular';
    public bool $isRecurring = false;
    public array $selectedDays = [];

    public function mount(): void
    {
        $this->authorize('viewAny', BusinessWorker::class);
    }

    public function create(): void
    {
        $this->authorize('create', BusinessWorker::class);

        $this->reset(['editingWorker', 'shiftStart', 'shiftEnd', 'shiftType', 'isRecurring', 'selectedDays']);
        $this->dispatch('open-modal', 'shift-modal');
    }

    public function edit(BusinessWorker $worker): void
    {
        $this->authorize('update', $worker);

        $this->editingWorker = $worker;
        $this->shiftStart = '';
        $this->shiftEnd = '';
        $this->shiftType = 'regular';
        $this->isRecurring = false;
        $this->selectedDays = [];
        $this->dispatch('open-modal', 'shift-modal');
    }

    public function saveShift(): void
    {
        $this->authorize('update', $this->editingWorker);

        $validated = $this->validate([
            'shiftStart' => 'required|date_format:H:i',
            'shiftEnd' => 'required|date_format:H:i|after:shiftStart',
            'shiftType' => 'required|in:regular,overtime,holiday',
            'isRecurring' => 'boolean',
            'selectedDays' => 'required_if:isRecurring,true|array|min:1',
        ]);

        // Save shift logic here - you'd create a Shift model
        // For now, we'll just show a success message
        
        Flux::toast(variant: 'success', text: __('Shift schedule saved successfully.'));
        $this->dispatch('close-modal', 'shift-modal');
        $this->reset(['editingWorker', 'shiftStart', 'shiftEnd', 'shiftType', 'isRecurring', 'selectedDays']);
    }

    public function getShiftsProperty()
    {
        $business = auth()->user()->businesses()->first();
        
        // This is a placeholder - you'd create a Shift model and query it
        $shifts = collect([
            [
                'id' => 1,
                'worker_name' => 'John Waiter',
                'shift_type' => 'regular',
                'start_time' => '09:00',
                'end_time' => '17:00',
                'date' => today()->format('Y-m-d'),
                'status' => 'scheduled',
            ],
            [
                'id' => 2,
                'worker_name' => 'Jane Stylist',
                'shift_type' => 'overtime',
                'start_time' => '10:00',
                'end_time' => '19:00',
                'date' => today()->format('Y-m-d'),
                'status' => 'in_progress',
            ],
            [
                'id' => 3,
                'worker_name' => 'Mike Waiter',
                'shift_type' => 'holiday',
                'start_time' => '08:00',
                'end_time' => '16:00',
                'date' => today()->subDay()->format('Y-m-d'),
                'status' => 'completed',
            ],
        ]);

        if ($this->search) {
            $shifts = $shifts->filter(function ($shift) {
                return str_contains(strtolower($shift['worker_name']), strtolower($this->search));
            });
        }

        if ($this->status !== 'all') {
            $shifts = $shifts->where('status', $this->status);
        }

        return $this->paginateCollection($shifts, 15);
    }

    public function getStatsProperty(): array
    {
        $business = auth()->user()->businesses()->first();
        
        // Placeholder stats - you'd calculate these from actual shift data
        return [
            'total_shifts' => 12,
            'scheduled_shifts' => 8,
            'in_progress_shifts' => 2,
            'completed_shifts' => 2,
            'workers_on_shift' => 2,
            'workers_scheduled' => 8,
        ];
    }

    public function getWorkersProperty()
    {
        $business = auth()->user()->businesses()->first();
        
        return BusinessWorker::where('business_id', $business->id)
                              ->where('is_active', true)
                              ->orderBy('name')
                              ->get(['id', 'name']);
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
        
        return view('livewire.manager.shift-management', [
            'shifts' => $this->shifts,
            'stats' => $this->stats,
            'workers' => $this->workers,
            'businessType' => $businessType,
            'workerTitle' => $workerTitle,
        ]);
    }
}
