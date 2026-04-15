<?php

namespace App\Livewire\Inventory;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class InventoryList extends Component
{
    use WithPagination;

    public string $search    = '';
    public string $branch_id = '';
    public string $category  = '';
    public string $stock     = '';  // 'low', 'out', 'ok'

    public bool $showAdjustModal = false;
    public ?int $adjustInventoryId = null;

    // Adjustment form
    public string $adjustType     = 'add';   // add | subtract | set
    public float  $adjustQty      = 0;
    public string $adjustNote     = '';

    public function updatingSearch(): void   { $this->resetPage(); }
    public function updatingBranchId(): void { $this->resetPage(); }
    public function updatingCategory(): void { $this->resetPage(); }
    public function updatingStock(): void    { $this->resetPage(); }

    public function openAdjust(int $inventoryId): void
    {
        $this->adjustInventoryId = $inventoryId;
        $this->adjustType  = 'add';
        $this->adjustQty   = 0;
        $this->adjustNote  = '';
        $this->showAdjustModal = true;
    }

    public function saveAdjustment(): void
    {
        $this->validate([
            'adjustQty'  => 'required|numeric|min:0',
            'adjustNote' => 'nullable|string|max:500',
        ]);

        $inv = Inventory::findOrFail($this->adjustInventoryId);
        $before = $inv->quantity;

        if ($this->adjustType === 'add') {
            $inv->increment('quantity', $this->adjustQty);
            $change = $this->adjustQty;
        } elseif ($this->adjustType === 'subtract') {
            $newQty = max(0, $inv->quantity - $this->adjustQty);
            $change = $newQty - $inv->quantity;
            $inv->update(['quantity' => $newQty]);
        } else {
            $change = $this->adjustQty - $inv->quantity;
            $inv->update(['quantity' => $this->adjustQty]);
        }

        InventoryMovement::create([
            'product_id'   => $inv->product_id,
            'product_variant_id' => $inv->product_variant_id,
            'branch_id'    => $inv->branch_id,
            'type'         => 'adjustment',
            'quantity'     => $change,
            'quantity_before' => $before,
            'quantity_after'  => $inv->fresh()->quantity,
            'notes'        => $this->adjustNote ?: __('Manual adjustment'),
            'user_id'      => Auth::id(),
            'reference_type' => null,
            'reference_id'   => null,
        ]);

        session()->flash('success', __('Stock adjusted successfully.'));
        $this->showAdjustModal = false;
        $this->adjustInventoryId = null;
        $this->adjustQty = 0;
        $this->adjustNote = '';
    }

    public function render()
    {
        $query = Inventory::with(['product.category', 'variant', 'branch'])
            ->when($this->search, function ($q) {
                $q->whereHas('product', fn($p) => $p->where('name', 'like', "%{$this->search}%")
                    ->orWhere('sku', 'like', "%{$this->search}%"));
            })
            ->when($this->branch_id, fn($q) => $q->where('branch_id', $this->branch_id))
            ->when($this->category, function ($q) {
                $q->whereHas('product', fn($p) => $p->where('category_id', $this->category));
            })
            ->when($this->stock === 'out', fn($q) => $q->where('quantity', '<=', 0))
            ->when($this->stock === 'low', fn($q) => $q->where('quantity', '>', 0)->whereRaw('quantity <= (select min_stock_alert from products where products.id = inventory.product_id)'))
            ->when($this->stock === 'ok', fn($q) => $q->whereRaw('quantity > (select min_stock_alert from products where products.id = inventory.product_id)'));

        $inventory   = $query->orderBy('updated_at', 'desc')->paginate(25);
        $branches    = Branch::where('is_active', true)->get();
        $categories  = Category::orderBy('name')->get();

        return view('livewire.inventory.inventory-list', compact('inventory', 'branches', 'categories'))
            ->layout('layouts.app', ['title' => 'Inventory']);
    }
}
