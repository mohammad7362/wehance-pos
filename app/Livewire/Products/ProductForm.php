<?php

namespace App\Livewire\Products;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProductForm extends Component
{
    use WithFileUploads;

    public ?Product $product = null;
    public bool $isEdit = false;

    public string $name = '';
    public string $description = '';
    public string $barcode = '';
    public string $sku = '';
    public ?int $category_id = null;
    public ?int $unit_id = null;
    public ?int $supplier_id = null;
    public string $cost_price = '0';
    public string $selling_price = '0';
    public string $tax_rate = '0';
    public string $min_stock_alert = '5';
    public string $pieces_per_box = '';
    public bool $track_inventory = true;
    public bool $is_active = true;
    public $image;
    public ?string $currentImage = null;

    // Initial stock (only on create)
    public string $initial_boxes = '0';
    public string $initial_pieces = '0';

    public function mount(?Product $product = null): void
    {
        if ($product && $product->exists) {
            $this->product = $product;
            $this->isEdit = true;
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
            $this->pieces_per_box = $product->pieces_per_box ? (string) $product->pieces_per_box : '';
            $this->track_inventory = (bool) $product->track_inventory;
            $this->is_active = (bool) $product->is_active;
            $this->currentImage = $product->image;
        }
    }

    protected function rules(): array
    {
        $ignoreId = $this->product?->id;

        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'barcode' => 'nullable|string|max:100|unique:products,barcode,' . ($ignoreId ?? 'NULL'),
            'sku' => 'nullable|string|max:100|unique:products,sku,' . ($ignoreId ?? 'NULL'),
            'category_id' => 'nullable|exists:categories,id',
            'unit_id' => 'nullable|exists:units,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'min_stock_alert' => 'required|integer|min:0',
            'pieces_per_box' => 'nullable|integer|min:1',
            'track_inventory' => 'boolean',
            'is_active' => 'boolean',
            'image' => 'nullable|image|max:2048',
            'initial_boxes' => 'integer|min:0',
            'initial_pieces' => 'integer|min:0',
        ];
    }

    public function save(): void
    {
        $this->validate();

        try {
            $data = [
                'name' => $this->name,
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

            if (Schema::hasColumn('products', 'pieces_per_box')) {
                $data['pieces_per_box'] = $this->pieces_per_box !== '' ? (int) $this->pieces_per_box : null;
            }

            if ($this->image) {
                $data['image'] = $this->image->store('products', 'public');
            }

            if ($this->isEdit && $this->product) {
                if ($this->product->name !== $this->name) {
                    $slug = Str::slug($this->name);
                    if (Product::where('slug', $slug)->where('id', '!=', $this->product->id)->exists()) {
                        $slug .= '-' . $this->product->id;
                    }
                    $data['slug'] = $slug;
                }
                $this->product->update($data);
                session()->flash('success', 'Product updated successfully.');
            } else {
                $slug = Str::slug($this->name);
                if (Product::where('slug', $slug)->exists()) {
                    $slug .= '-' . time();
                }
                $data['slug'] = $slug;
                $product = Product::create($data);

                // Seed initial stock / always create inventory row for branch
                $branchId = Auth::user()?->branch_id
                    ?? \App\Models\Branch::where('is_active', true)->value('id');

                if ($branchId) {
                    $piecesPerBox = max(1, (int) ($product->pieces_per_box ?? 1));
                    $boxes  = max(0, (int) $this->initial_boxes);
                    $pieces = max(0, (int) $this->initial_pieces);
                    $totalQty = $product->track_inventory ? ($boxes * $piecesPerBox) + $pieces : 0;

                    $inventory = Inventory::firstOrCreate([
                            'product_id' => $product->id,
                            'product_variant_id' => null,
                            'branch_id' => $branchId,
                        ], [
                            'quantity' => 0,
                        ]);

                    $beforeQty = (float) ($inventory->quantity ?? 0);

                    if ($totalQty > 0) {
                        $inventory->increment('quantity', $totalQty);
                        $afterQty = $beforeQty + $totalQty;

                        InventoryMovement::create([
                            'product_id' => $product->id,
                            'product_variant_id' => null,
                            'branch_id' => $branchId,
                            'user_id' => Auth::id(),
                            'type' => 'initial',
                            'quantity' => $totalQty,
                            'quantity_before' => $beforeQty,
                            'quantity_after' => $afterQty,
                            'notes' => 'Initial stock (' . $this->initial_boxes . ' boxes + ' . $this->initial_pieces . ' pieces)',
                        ]);
                    }
                }

                session()->flash('success', 'Product created successfully.');
            }

            $this->redirectRoute('products.index');
        } catch (\Throwable $e) {
            report($e);
            session()->flash('error', 'Product could not be saved. If this is production, run migrations and clear caches.');
        }
    }

    public function render()
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $units = Unit::orderBy('name')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        return view('livewire.products.product-form', compact('categories', 'units', 'suppliers'))
            ->layout('layouts.app', ['title' => $this->isEdit ? 'Edit Product' : 'New Product']);
    }
}
