<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'reference_no', 'supplier_id', 'branch_id', 'created_by', 'status',
        'total_amount', 'paid_amount', 'discount_amount', 'tax_amount',
        'shipping_cost', 'notes', 'ordered_at', 'expected_at', 'received_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'ordered_at' => 'date',
        'expected_at' => 'date',
        'received_at' => 'date',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function getDueAmountAttribute(): float
    {
        return (float) ($this->total_amount - $this->paid_amount);
    }

    protected static function booted(): void
    {
        static::creating(function (self $po) {
            if (empty($po->reference_no)) {
                $po->reference_no = 'PO-' . strtoupper(uniqid());
            }
        });
    }
}
