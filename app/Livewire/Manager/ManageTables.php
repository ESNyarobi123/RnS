<?php

namespace App\Livewire\Manager;

use App\Models\Table;
use App\Services\QrCodeService;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Tables')]
class ManageTables extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = 'all';

    public ?Table $editingTable = null;
    public string $tableName = '';
    public int $capacity = 4;
    public string $statusFilter = 'available';

    protected $queryString = ['search', 'status'];

    public function mount(): void
    {
        $this->authorize('viewAny', Table::class);
    }

    public function create(): void
    {
        $this->authorize('create', Table::class);

        $this->reset(['editingTable', 'tableName', 'capacity', 'statusFilter']);
        $this->dispatch('open-modal', 'table-modal');
    }

    public function edit(Table $table): void
    {
        $this->authorize('update', $table);

        $this->editingTable = $table;
        $this->tableName = $table->name;
        $this->capacity = $table->capacity;
        $this->statusFilter = $table->status;
        $this->dispatch('open-modal', 'table-modal');
    }

    public function save(): void
    {
        if ($this->editingTable) {
            $this->authorize('update', $this->editingTable);
        } else {
            $this->authorize('create', Table::class);
        }

        $validated = $this->validate([
            'tableName' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1|max:20',
            'statusFilter' => 'required|in:available,occupied,reserved',
        ]);

        $business = Auth::user()->businesses()->first();

        if ($this->editingTable) {
            $this->editingTable->update([
                'name' => $validated['tableName'],
                'capacity' => $validated['capacity'],
                'status' => $validated['statusFilter'],
            ]);

            Flux::toast(variant: 'success', text: __(':label updated successfully.', ['label' => $business->tableLabel()]));
        } else {
            $table = Table::create([
                'business_id' => $business->id,
                'name' => $validated['tableName'],
                'capacity' => $validated['capacity'],
                'status' => $validated['statusFilter'],
            ]);

            // Generate QR code for the table
            $qrCodeService = app(QrCodeService::class);
            $qrCodeService->generateTableQrCode($table);

            Flux::toast(variant: 'success', text: __(':label created successfully.', ['label' => $business->tableLabel()]));
        }

        $this->dispatch('close-modal', 'table-modal');
        $this->reset(['editingTable', 'tableName', 'capacity', 'statusFilter']);
    }

    public function delete(Table $table): void
    {
        $this->authorize('delete', $table);

        $table->delete();
        Flux::toast(variant: 'success', text: __('Table deleted successfully.'));
    }

    public function regenerateQrCode(Table $table): void
    {
        $this->authorize('update', $table);

        $qrCodeService = app(QrCodeService::class);
        $qrCode = $qrCodeService->generateTableQrCode($table);

        Flux::toast(variant: 'success', text: __('QR code regenerated successfully.'));
    }

    public function toggleStatus(Table $table): void
    {
        $this->authorize('update', $table);

        $newStatus = $table->status === 'available' ? 'occupied' : 'available';
        $table->update(['status' => $newStatus]);

        $statusText = $newStatus === 'available' ? __('available') : __('occupied');
        Flux::toast(variant: 'success', text: __(':label marked as :status.', [
            'label' => $table->business->tableLabel(),
            'status' => $statusText,
        ]));
    }

    public function getTablesProperty()
    {
        $business = Auth::user()->businesses()->first();
        
        $query = Table::where('business_id', $business->id)
            ->with(['qrCodes']);

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        if ($this->status !== 'all') {
            $query->where('status', $this->status);
        }

        return $query->orderBy('name')->paginate(10);
    }

    public function getStatsProperty(): array
    {
        $business = Auth::user()->businesses()->first();
        
        $total = Table::where('business_id', $business->id)->count();
        $available = Table::where('business_id', $business->id)->where('status', 'available')->count();
        $occupied = Table::where('business_id', $business->id)->where('status', 'occupied')->count();
        $reserved = Table::where('business_id', $business->id)->where('status', 'reserved')->count();

        return [
            'total' => $total,
            'available' => $available,
            'occupied' => $occupied,
            'reserved' => $reserved,
        ];
    }

    public function render()
    {
        $business = Auth::user()->businesses()->first();
        
        return view('livewire.manager.manage-tables', [
            'tables' => $this->tables,
            'stats' => $this->stats,
            'title' => $business?->tableLabelPlural() ?? __('Tables'),
            'singleLabel' => $business?->tableLabel() ?? __('Table'),
        ]);
    }
}
