<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = [
        'name', 'code', 'address', 'city', 'phone', 'email',
        'currency', 'currency_symbol', 'secondary_currency', 'secondary_currency_symbol', 'exchange_rate', 'tax_rate', 'receipt_footer', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'exchange_rate' => 'decimal:4',
        'tax_rate' => 'decimal:2',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function inventory()
    {
        return $this->hasMany(Inventory::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}
