<?php

namespace App\Livewire\Purchases;

use App\Models\Branch;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class PurchaseForm extends Component
{
    public ?PurchaseOrder $po = null;
    public bool $isEdit = false;

    // Header
    public ?int   $supplier_id   = null;
    public ?int   $branch_id     = null;
    public string $ordered_at    = '';
    public string $expected_at   = '';
    public string $notes         = '';

    // Line items
    public array $items = [];

    // Product search
    public string $productSearch   = '';
    public array  $productResults  = [];

    public function getIsReadOnlyProperty(): bool
    {
        return $this->isEdit && $this->po?->status === 'received';
    }

    protected function rules(): array
    {
        return [
            'supplier_id'   => 'required|exists:suppliers,id',
            'branch_id'     => 'required|exists:branches,id',
            'ordered_at'    => 'required|date',
            'expected_at'   => 'nullable|date|after_or_equal:ordered_at',
            'notes'         => 'nullable|string|max:1000',
            'items'         => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|numeric|min:1',
            'items.*.unit_cost'  => 'required|numeric|min:0',
        ];
    }

    public function mount(?PurchaseOrder $po = null): void
    {
        $this->ordered_at = now()->toDateString();
        if ($po && $po->exists) {
            $this->po           = $po;
            $this->isEdit       = true;
            $this->supplier_id  = $po->supplier_id;
            $this->branch_id    = $po->branch_id;
            $this->ordered_at   = $po->ordered_at ? now()->parse((string) $po->ordered_at)->toDateString() : now()->toDateString();
            $this->expected_at  = $po->expected_at ? now()->parse((string) $po->expected_at)->toDateString() : '';
            $this->notes        = $po->notes ?? '';
            $this->items        = $po->items->map(fn($i) => [
                'product_id'   => $i->product_id,
                'product_name' => $i->product->name,
                'quantity'     => $i->quantity,
                'unit_cost'    => $i->unit_cost,
                'selling_price' => $i->selling_price,
            ])->toArray();
        }

        if (empty($this->items)) {
            $this->items = [];
        }

        if (!$this->branch_id) {
            $this->branch_id = Auth::user()->branch_id ?? Branch::first()?->id;
        }
    }

    public function searchProducts(): void
    {
        if ($this->isReadOnly) {
            $this->productResults = [];
            return;
        }

        if (strlen($this->productSearch) < 2) {
            $this->productResults = [];
            return;
        }
        $this->productResults = Product::where('is_active', true)
            ->where(fn($q) => $q->where('name', 'like', "%{$this->productSearch}%")
                ->orWhere('sku', 'like', "%{$this->productSearch}%"))
            ->limit(10)
            ->get(['id', 'name', 'sku', 'cost_price'])
            ->toArray();
    }

    public function addProduct(int $productId): void
    {
        if ($this->isReadOnly) {
            return;
        }

        $product = Product::find($productId);
        if (!$product) {
            return;
        }

        foreach ($this->items as $key => $item) {
            if ($item['product_id'] === $productId) {
                $this->items[$key]['quantity']++;
                $this->productSearch   = '';
                $this->productResults  = [];
                return;
            }
        }

        $this->items[] = [
            'product_id'   => $product->id,
            'product_name' => $product->name,
            'quantity'     => 1,
            'unit_cost'    => $product->cost_price ?? 0,
            'selling_price' => $product->selling_price ?? 0,
        ];

        $this->productSearch  = '';
        $this->productResults = [];
    }

    public function removeItem(int $index): void
    {
        if ($this->isReadOnly) {
            return;
        }

        array_splice($this->items, $index, 1);
        $this->items = array_values($this->items);
    }

    public function getSubtotalProperty(): float
    {
        return collect($this->items)->sum(fn($i) => ($i['quantity'] ?? 0) * ($i['unit_cost'] ?? 0));
    }

    public function getTaxTotalProperty(): float
    {
        return 0;
    }

    public function getTotalProperty(): float
    {
        return $this->subtotal + $this->taxTotal;
    }

    public function saveDraft(): void
    {
        $this->saveWithStatus('draft');
    }

    public function submitOrder(): void
    {
        $this->saveWithStatus('sent');
    }

    public function receiveOrder(): void
    {
        if (!$this->po) return;
        $this->saveWithStatus('received');
    }

    private function saveWithStatus(string $status): void
    {
        if ($this->isReadOnly) {
            session()->flash('error', 'Received purchase orders cannot be edited because inventory has already been posted.');
            return;
        }

        $this->validate();

        DB::transaction(function () use ($status) {
            $poData = [
                'supplier_id'   => $this->supplier_id,
                'branch_id'     => $this->branch_id,
                'ordered_at'    => $this->ordered_at,
                'expected_at'   => $this->expected_at ?: null,
                'notes'         => $this->notes ?: null,
                'discount_amount' => 0,
                'tax_amount'      => 0,
                'shipping_cost'   => 0,
                'total_amount'  => $this->total,
                'status'        => $status,
                'created_by'    => $this->isEdit ? $this->po->created_by : Auth::id(),
            ];

            if ($this->isEdit) {
                $this->po->update($poData);
                $this->po->items()->delete();
            } else {
                $poData['reference_no'] = 'PO-' . strtoupper(uniqid());
                $this->po = PurchaseOrder::create($poData);
            }

            foreach ($this->items as $item) {
                $this->po->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                    'unit_cost'  => $item['unit_cost'],
                    'selling_price' => $item['selling_price'] ?? 0,
                    'total_cost' => $item['quantity'] * $item['unit_cost'],
                ]);
            }

            // If received, update inventory
            if ($status === 'received') {
                foreach ($this->items as $item) {
                    $inv = Inventory::firstOrCreate(
                        ['product_id' => $item['product_id'], 'branch_id' => $this->branch_id, 'product_variant_id' => null],
                        ['quantity' => 0]
                    );
                    $before = $inv->quantity;
                    $inv->increment('quantity', $item['quantity']);

                    InventoryMovement::create([
                        'product_id'     => $item['product_id'],
                        'branch_id'      => $this->branch_id,
                        'product_variant_id' => null,
                        'type'           => 'purchase',
                        'quantity'       => $item['quantity'],
                        'quantity_before' => $before,
                        'quantity_after'  => $before + $item['quantity'],
                        'unit_cost'      => $item['unit_cost'],
                        'notes'          => 'PO: ' . $this->po->reference_no,
                        'user_id'        => Auth::id(),
                        'reference_type' => PurchaseOrder::class,
                        'reference_id'   => $this->po->id,
                    ]);
                }
            }
        });

        session()->flash('success', 'Purchase order saved.');
        $this->redirect(route('purchases.index'));
    }

    public function render()
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $branches  = Branch::where('is_active', true)->get();

        return view('livewire.purchases.purchase-form', compact('suppliers', 'branches'))
            ->layout('layouts.app', ['title' => $this->isEdit ? 'Edit Purchase Order' : 'New Purchase Order']);
    }
}
