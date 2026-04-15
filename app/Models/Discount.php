<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    protected $fillable = [
        'name', 'code', 'type', 'value', 'min_purchase', 'max_discount',
        'usage_limit', 'usage_per_customer', 'used_count',
        'applies_to', 'is_active', 'starts_at', 'expires_at',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_purchase' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(DiscountItem::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function isValid(): bool
    {
        if (! $this->is_active) return false;
        $now = now();
        if ($this->starts_at && $now->lt($this->starts_at)) return false;
        if ($this->expires_at && $now->gt($this->expires_at)) return false;
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) return false;
        return true;
    }

    public function calculateDiscount(float $subtotal): float
    {
        if ($this->min_purchase && $subtotal < $this->min_purchase) {
            return 0;
        }

        return $this->baseDiscountAmount($subtotal);
    }

    public function calculateDiscountForCart(array $cart): float
    {
        $eligibleSubtotal = $this->eligibleSubtotalForCart($cart);

        if ($this->min_purchase && $eligibleSubtotal < $this->min_purchase) {
            return 0;
        }

        return $this->baseDiscountAmount($eligibleSubtotal);
    }

    public function eligibleSubtotalForCart(array $cart): float
    {
        $cartItems = collect($cart);

        if ($cartItems->isEmpty()) {
            return 0;
        }

        $lineTotal = fn (array $item): float => (float) ((($item['qty'] ?? 0) * ($item['price'] ?? 0)) - ($item['discount'] ?? 0));

        if ($this->applies_to === 'all') {
            return (float) $cartItems->sum($lineTotal);
        }

        $applicableType = $this->applies_to === 'category' ? Category::class : Product::class;
        $selectedIds = $this->items()
            ->where('applicable_type', $applicableType)
            ->pluck('applicable_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($selectedIds === []) {
            return 0;
        }

        if ($this->applies_to === 'product') {
            return (float) $cartItems
                ->filter(fn (array $item) => in_array((int) ($item['product_id'] ?? 0), $selectedIds, true))
                ->sum($lineTotal);
        }

        $productCategoryMap = Product::query()
            ->whereIn('id', $cartItems->pluck('product_id')->filter()->unique())
            ->pluck('category_id', 'id')
            ->map(fn ($categoryId) => $categoryId === null ? null : (int) $categoryId)
            ->all();

        return (float) $cartItems
            ->filter(function (array $item) use ($productCategoryMap, $selectedIds): bool {
                $categoryId = $productCategoryMap[(int) ($item['product_id'] ?? 0)] ?? null;

                return $categoryId !== null && in_array($categoryId, $selectedIds, true);
            })
            ->sum($lineTotal);
    }

    private function baseDiscountAmount(float $subtotal): float
    {
        $discount = $this->type === 'percentage'
            ? $subtotal * ($this->value / 100)
            : $this->value;

        if ($this->max_discount) {
            $discount = min($discount, $this->max_discount);
        }

        return round($discount, 2);
    }
}
