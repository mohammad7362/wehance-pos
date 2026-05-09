<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id', 'unit_id', 'supplier_id', 'name', 'slug', 'description',
        'barcode', 'sku', 'cost_price', 'selling_price', 'tax_rate', 'image',
        'min_stock_alert', 'pieces_per_box', 'has_variants', 'track_inventory', 'is_active',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'has_variants' => 'boolean',
        'track_inventory' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function inventory()
    {
        return $this->hasMany(Inventory::class);
    }

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getStockForBranch(int $branchId): float
    {
        return (float) $this->inventory()->where('branch_id', $branchId)->sum('quantity');
    }
}
