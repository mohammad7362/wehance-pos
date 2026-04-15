<?php

namespace App\Livewire\Discounts;

use App\Models\Category;
use App\Models\Discount;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class DiscountList extends Component
{
    use WithPagination;

    public string $search = '';
    public bool   $showModal = false;
    public bool   $showDeleteModal = false;
    public ?int   $editingId  = null;
    public ?int   $deletingId = null;

    // Form
    public string  $name          = '';
    public string  $code          = '';
    public string  $type          = 'percentage'; // percentage | fixed
    public float   $value         = 0;
    public float   $min_purchase  = 0;
    public float   $max_discount  = 0;
    public ?int    $usage_limit   = null;
    public string  $starts_at     = '';
    public string  $expires_at    = '';
    public bool    $is_active     = true;
    public string  $applies_to    = 'all'; // all | products | categories
    public array   $selectedItems = [];

    protected function rules(): array
    {
        $itemExistsRule = $this->applies_to === 'category'
            ? 'integer|exists:categories,id'
            : 'integer|exists:products,id';

        return [
            'name'         => 'required|string|max:150',
            'code'         => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('discounts', 'code')->ignore($this->editingId),
            ],
            'type'         => 'required|in:percentage,fixed',
            'value'        => 'required|numeric|min:0',
            'min_purchase' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'usage_limit'  => 'nullable|integer|min:1',
            'applies_to'   => 'required|in:all,category,product',
            'starts_at'    => 'nullable|date',
            'expires_at'   => 'nullable|date|after_or_equal:starts_at',
            'is_active'    => 'boolean',
            'selectedItems' => Rule::requiredIf($this->applies_to !== 'all'),
            'selectedItems.*' => $itemExistsRule,
        ];
    }

    public function updatingSearch(): void { $this->resetPage(); }

    public function updatedAppliesTo(): void
    {
        $this->selectedItems = [];
        $this->resetValidation('selectedItems');
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $d = Discount::with('items')->findOrFail($id);
        $this->editingId     = $id;
        $this->name          = $d->name;
        $this->code          = $d->code ?? '';
        $this->type          = $d->type;
        $this->value         = (float) ($d->value ?? 0);
        $this->min_purchase  = (float) ($d->min_purchase ?? 0);
        $this->max_discount  = (float) ($d->max_discount ?? 0);
        $this->usage_limit   = $d->usage_limit;
        $this->applies_to    = $d->applies_to;
        $this->selectedItems = $d->items
            ->where('applicable_type', $d->applies_to === 'category' ? Category::class : Product::class)
            ->pluck('applicable_id')
            ->map(fn ($itemId) => (int) $itemId)
            ->values()
            ->all();
        $this->starts_at     = $d->starts_at?->toDateString() ?? '';
        $this->expires_at    = $d->expires_at?->toDateString() ?? '';
        $this->is_active     = (bool) $d->is_active;
        $this->showModal     = true;
    }

    public function save(): void
    {
        $this->validate();

        $selectedItems = collect($this->selectedItems)
            ->map(fn ($itemId) => (int) $itemId)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $data = [
            'name'         => $this->name,
            'code'         => ($normalizedCode = strtoupper(trim($this->code))) !== '' ? $normalizedCode : null,
            'type'         => $this->type,
            'value'        => $this->value,
            'min_purchase' => $this->min_purchase ?: null,
            'max_discount' => $this->max_discount ?: null,
            'usage_limit'  => $this->usage_limit,
            'applies_to'   => $this->applies_to,
            'starts_at'    => $this->starts_at ?: null,
            'expires_at'   => $this->expires_at ?: null,
            'is_active'    => $this->is_active,
        ];

        DB::transaction(function () use ($data, $selectedItems) {
            if ($this->editingId) {
                $discount = Discount::findOrFail($this->editingId);
                $discount->update($data);
            } else {
                $discount = Discount::create($data);
            }

            $discount->items()->delete();

            if ($this->applies_to !== 'all') {
                $applicableType = $this->applies_to === 'category' ? Category::class : Product::class;

                $discount->items()->createMany(
                    collect($selectedItems)
                        ->map(fn ($itemId) => [
                            'applicable_type' => $applicableType,
                            'applicable_id' => $itemId,
                        ])
                        ->all()
                );
            }
        });

        session()->flash('success', 'Discount saved.');
        $this->showModal = false;
        $this->resetForm();
    }

    public function toggleActive(int $id): void
    {
        $d = Discount::findOrFail($id);
        $d->update(['is_active' => !$d->is_active]);
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            Discount::findOrFail($this->deletingId)->delete();
            session()->flash('success', 'Discount deleted.');
        }
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = $this->code = $this->starts_at = $this->expires_at = '';
        $this->type = 'percentage';
        $this->value = $this->min_purchase = $this->max_discount = 0;
        $this->usage_limit = null;
        $this->applies_to = 'all';
        $this->is_active = true;
        $this->selectedItems = [];
        $this->resetValidation();
    }

    public function render()
    {
        $discounts = Discount::when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('code', 'like', "%{$this->search}%"))
            ->latest()
            ->paginate(20);

        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $products = Product::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'sku']);

        return view('livewire.discounts.discount-list', compact('discounts', 'categories', 'products'))
            ->layout('layouts.app', ['title' => 'Discounts & Coupons']);
    }
}
