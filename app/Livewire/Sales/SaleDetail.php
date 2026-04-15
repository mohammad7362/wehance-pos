<?php

namespace App\Livewire\Sales;

use App\Models\Sale;
use Livewire\Component;

class SaleDetail extends Component
{
    public Sale $sale;

    public function mount(Sale $sale): void
    {
        $this->sale = $sale->load(['customer', 'branch', 'cashier', 'items.product', 'items.variant', 'payments']);
    }

    public function render()
    {
        return view('livewire.sales.sale-detail')
            ->layout('layouts.app', ['title' => 'Sale #' . $this->sale->reference_no]);
    }
}
