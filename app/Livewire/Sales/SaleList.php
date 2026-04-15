<?php

namespace App\Livewire\Sales;

use App\Models\Branch;
use App\Models\Sale;
use Livewire\Component;
use Livewire\WithPagination;

class SaleList extends Component
{
    use WithPagination;

    public string $search    = '';
    public string $branch_id = '';
    public string $status    = '';
    public string $dateFrom  = '';
    public string $dateTo    = '';

    public function updatingSearch(): void  { $this->resetPage(); }
    public function updatingStatus(): void  { $this->resetPage(); }

    public function voidSale(int $id): void
    {
        $sale = Sale::findOrFail($id);
        if ($sale->status !== 'completed') {
            return;
        }
        $sale->update(['status' => 'cancelled']);

        // Restore inventory
        foreach ($sale->items as $item) {
            \App\Models\Inventory::where('product_id', $item->product_id)
                ->where('branch_id', $sale->branch_id)
                ->increment('quantity', $item->quantity);
        }

        session()->flash('success', __('Sale #:reference cancelled.', ['reference' => $sale->reference_no]));
    }

    public function render()
    {
        $sales = Sale::with(['customer', 'branch', 'cashier'])
            ->when($this->search, function ($q) {
                $q->where('reference_no', 'like', "%{$this->search}%")
                  ->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%{$this->search}%"));
            })
            ->when($this->branch_id, fn($q) => $q->where('branch_id', $this->branch_id))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->latest()
            ->paginate(25);

        $branches = Branch::where('is_active', true)->get();

        return view('livewire.sales.sale-list', compact('sales', 'branches'))
            ->layout('layouts.app', ['title' => 'Sales']);
    }
}
