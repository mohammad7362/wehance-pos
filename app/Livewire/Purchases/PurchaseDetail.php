<?php

namespace App\Livewire\Purchases;

use App\Models\PurchaseOrder;
use Livewire\Component;

class PurchaseDetail extends Component
{
    public PurchaseOrder $po;

    public function mount(PurchaseOrder $po): void
    {
        $this->po = $po->load(['supplier', 'branch', 'creator', 'items.product']);
    }

    public function render()
    {
        return view('livewire.purchases.purchase-detail')
            ->layout('layouts.app', ['title' => 'PO #' . $this->po->reference_no]);
    }
}
