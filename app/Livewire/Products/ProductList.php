<?php

namespace App\Livewire\Products;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class ProductList extends Component
{
    use WithPagination, WithFileUploads;

    public string $search = '';
    public string $categoryFilter = '';
    public string $statusFilter = '';
    public string $sortBy = 'name';
    public string $sortDir = 'asc';
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $deletingId = null;

    // Form fields
    public ?int $editingId = null;
    public string $name = '';
    public string $description = '';
    public string $barcode = '';
    public string $sku = '';
    public ?int $category_id = null;
    public ?int $unit_id = null;
    public ?int $supplier_id = null;
    public string $cost_price = '';
    public string $selling_price = '';
    public string $tax_rate = '0';
    public string $min_stock_alert = '5';
    public bool $track_inventory = true;
    public bool $is_active = true;
    public $image;
    public string $initial_stock = '0';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'barcode' => 'nullable|string|max:100|unique:products,barcode,' . ($this->editingId ?? 'NULL'),
            'sku' => 'nullable|string|max:100|unique:products,sku,' . ($this->editingId ?? 'NULL'),
            'category_id' => 'nullable|exists:categories,id',
            'unit_id' => 'nullable|exists:units,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'min_stock_alert' => 'required|integer|min:0',
            'track_inventory' => 'boolean',
            'is_active' => 'boolean',
            'image' => 'nullable|image|max:2048',
            'initial_stock' => 'integer|min:0',
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDir = 'asc';
        }
    }

    public function openCreate(): void
    {
        $this->reset(['editingId', 'name', 'description', 'barcode', 'sku', 'category_id',
            'unit_id', 'supplier_id', 'cost_price', 'selling_price', 'tax_rate', 'min_stock_alert',
            'track_inventory', 'is_active', 'image', 'initial_stock']);
        $this->track_inventory = true;
        $this->is_active = true;
        $this->tax_rate = '0';
        $this->min_stock_alert = '5';
        $this->initial_stock = '0';
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $product = Product::findOrFail($id);

        $this->editingId = $product->id;
        $this->name = $product->name;
        $this->description = $product->description ?? '';
        $this->barcode = $product->barcode ?? '';
        $this->sku = $product->sku ?? '';
        $this->category_id = $product->category_id;
        $this->unit_id = $product->unit_id;
        $this->supplier_id = $product->supplier_id;
        $this->cost_price = (string) $product->cost_price;
        $this->selling_price = (string) $product->selling_price;
        $this->tax_rate = (string) $product->tax_rate;
        $this->min_stock_alert = (string) $product->min_stock_alert;
        $this->track_inventory = $product->track_inventory;
        $this->is_active = $product->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $slug = \Illuminate\Support\Str::slug($this->name);

        $data = [
            'name' => $this->name,
            'slug' => $slug,
            'description' => $this->description ?: null,
            'barcode' => $this->barcode ?: null,
            'sku' => $this->sku ?: null,
            'category_id' => $this->category_id,
            'unit_id' => $this->unit_id,
            'supplier_id' => $this->supplier_id,
            'cost_price' => $this->cost_price,
            'selling_price' => $this->selling_price,
            'tax_rate' => $this->tax_rate,
            'min_stock_alert' => $this->min_stock_alert,
            'track_inventory' => $this->track_inventory,
            'is_active' => $this->is_active,
        ];

        if ($this->image) {
            $data['image'] = $this->image->store('products', 'public');
        }

        if ($this->editingId) {
            $product = Product::findOrFail($this->editingId);

            // Ensure unique slug for edits
            if ($product->name !== $this->name) {
                $data['slug'] = \Illuminate\Support\Str::slug($this->name) . '-' . $this->editingId;
            }
            $product->update($data);
            session()->flash('success', 'Product updated successfully.');
        } else {
            // Make slug unique
            $existingSlug = Product::where('slug', $slug)->exists();
            if ($existingSlug) {
                $data['slug'] = $slug . '-' . time();
            }
            $product = Product::create($data);

            $branchId = Auth::user()?->branch_id
                ?? \App\Models\Branch::where('is_active', true)->value('id');

            if ($branchId) {
                $qty = max(0, (int) $this->initial_stock);
                $inventory = Inventory::firstOrCreate([
                    'product_id' => $product->id,
                    'product_variant_id' => null,
                    'branch_id' => $branchId,
                ], [
                    'quantity' => 0,
                ]);
                if ($qty > 0 && $product->track_inventory) {
                    $inventory->increment('quantity', $qty);
                }
            }

            session()->flash('success', 'Product created successfully.');
        }

        $this->showModal = false;
        $this->reset(['editingId', 'name', 'description', 'barcode', 'sku']);
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        Product::findOrFail($this->deletingId)->delete();

        $this->showDeleteModal = false;
        $this->deletingId = null;
        session()->flash('success', 'Product deleted.');
    }

    public function toggleStatus(int $id): void
    {
        $product = Product::findOrFail($id);
        $product->update(['is_active' => ! $product->is_active]);
    }

    public function render()
    {
        $branchId = Auth::user()?->branch_id;

        $products = Product::with([
                'category',
                'unit',
                'inventory' => fn ($q) => $q->where('branch_id', $branchId)->whereNull('product_variant_id'),
            ])
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('barcode', 'like', "%{$this->search}%")
                  ->orWhere('sku', 'like', "%{$this->search}%");
            }))
            ->when($this->categoryFilter, fn($q) => $q->where('category_id', $this->categoryFilter))
            ->when($this->statusFilter !== '', fn($q) => $q->where('is_active', $this->statusFilter === '1'))
            ->orderBy($this->sortBy, $this->sortDir)
            ->paginate(15);

        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $units = Unit::orderBy('name')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        return view('livewire.products.product-list', compact('products', 'categories', 'units', 'suppliers'))
            ->layout('layouts.app');
    }
}
