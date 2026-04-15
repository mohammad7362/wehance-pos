<?php

namespace App\Livewire\Purchases;

use App\Models\Branch;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Livewire\Component;
use Livewire\WithPagination;

class PurchaseList extends Component
{
    use WithPagination;

    public string $search      = '';
    public string $supplier_id = '';
    public string $status      = '';
    public string $dateFrom    = '';
    public string $dateTo      = '';

    public function updatingSearch(): void    { $this->resetPage(); }
    public function updatingStatus(): void    { $this->resetPage(); }

    public function render()
    {
        $orders = PurchaseOrder::with(['supplier', 'branch', 'creator'])
            ->when($this->search, function ($q) {
                $q->where('reference_no', 'like', "%{$this->search}%")
                  ->orWhereHas('supplier', fn($s) => $s->where('name', 'like', "%{$this->search}%"));
            })
            ->when($this->supplier_id, fn($q) => $q->where('supplier_id', $this->supplier_id))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->dateFrom, fn($q) => $q->whereDate('ordered_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('ordered_at', '<=', $this->dateTo))
            ->latest()
            ->paginate(20);

        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        return view('livewire.purchases.purchase-list', compact('orders', 'suppliers'))
            ->layout('layouts.app', ['title' => 'Purchase Orders']);
    }
}
