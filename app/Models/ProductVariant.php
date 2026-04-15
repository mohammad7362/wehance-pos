<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id', 'name', 'barcode', 'sku', 'cost_price', 'selling_price', 'image', 'is_active',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function inventory()
    {
        return $this->hasMany(Inventory::class, 'product_variant_id');
    }

    public function getStockForBranch(int $branchId): float
    {
        return (float) $this->inventory()->where('branch_id', $branchId)->sum('quantity');
    }
}
