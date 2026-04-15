<?php

namespace App\Livewire\Inventory;

use App\Models\Branch;
use App\Models\InventoryMovement;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class MovementList extends Component
{
    use WithPagination;

    public string $search    = '';
    public string $branch_id = '';
    public string $type      = '';
    public string $dateFrom  = '';
    public string $dateTo    = '';

    public function updatingSearch(): void  { $this->resetPage(); }
    public function updatingType(): void    { $this->resetPage(); }
    public function updatingBranchId(): void { $this->resetPage(); }

    public function render()
    {
        $movements = InventoryMovement::with(['product', 'variant', 'branch', 'user'])
            ->when($this->search, function ($q) {
                $q->whereHas('product', fn($p) => $p->where('name', 'like', "%{$this->search}%"));
            })
            ->when($this->branch_id, fn($q) => $q->where('branch_id', $this->branch_id))
            ->when($this->type, fn($q) => $q->where('type', $this->type))
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->latest()
            ->paginate(30);

        $branches = Branch::where('is_active', true)->get();

        $types = ['sale', 'purchase', 'adjustment', 'return', 'transfer_in', 'transfer_out', 'initial'];

        return view('livewire.inventory.movement-list', compact('movements', 'branches', 'types'))
            ->layout('layouts.app', ['title' => 'Inventory Movements']);
    }
}
