<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'reference_no', 'branch_id', 'user_id', 'customer_id', 'discount_id',
        'status', 'subtotal', 'discount_amount', 'tax_amount', 'total',
        'amount_paid', 'change_amount', 'loyalty_points_earned',
        'loyalty_points_redeemed', 'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'change_amount' => 'decimal:2',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function discount()
    {
        return $this->belongsTo(Discount::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    protected static function booted(): void
    {
        static::creating(function (self $sale) {
            if (empty($sale->reference_no)) {
                $sale->reference_no = 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
            }
        });
    }
}
