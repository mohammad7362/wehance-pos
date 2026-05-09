<?php

namespace App\Livewire\Pos;

use App\Models\AppSetting;
use App\Models\Customer;
use App\Models\Discount;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Checkout extends Component
{
    public string $currencyCode = 'USD';
    public string $currencySymbol = '$';
    public ?string $secondaryCurrencyCode = null;
    public ?string $secondaryCurrencySymbol = null;
    public ?float $exchangeRate = null;

    // Cart
    public array $cart = [];
    public string $barcodeInput = '';
    public string $productSearch = '';
    public array $searchResults = [];
    public bool $showSearchResults = false;

    // Customer
    public ?int $customerId = null;
    public string $customerSearch = '';
    public array $customerResults = [];
    public bool $showCustomerResults = false;

    // Discount
    public string $couponCode = '';
    public ?int $discountId = null;
    public ?int $autoDiscountId = null;
    public float $discountAmount = 0;
    public string $couponMessage = '';

    // Payment
    public string $paymentMethod = 'cash';
    public string $cashTendered = '';
    public bool $showPaymentModal = false;
    public bool $showReceiptModal = false;
    public ?int $completedSaleId = null;

    // Notes
    public string $notes = '';

    public function mount(): void
    {
        $branch = Auth::user()?->branch;

        $this->currencyCode = $branch?->currency ?? 'USD';
        $this->currencySymbol = $branch?->currency_symbol ?? '$';
        $this->secondaryCurrencyCode = $branch?->secondary_currency ?: null;
        $this->secondaryCurrencySymbol = $branch?->secondary_currency_symbol ?: null;
        $this->exchangeRate = $branch?->exchange_rate !== null ? (float) $branch->exchange_rate : null;
    }

    public function updatedBarcodeInput(string $value): void
    {
        if (strlen($value) >= 3) {
            $product = Product::where('barcode', $value)
                ->where('is_active', true)
                ->first();
            if ($product) {
                $this->addToCart($product->id);
                $this->barcodeInput = '';
            }
        }
    }

    public function updatedProductSearch(string $value): void
    {
        if (strlen($value) >= 2) {
            $branchId = Auth::user()?->branch_id;
            $this->searchResults = Product::with(['inventory' => fn($q) => $q->where('branch_id', $branchId)->whereNull('product_variant_id')])
                ->where('is_active', true)
                ->where(function ($q) use ($value) {
                    $q->where('name', 'like', "%{$value}%")
                      ->orWhere('barcode', 'like', "%{$value}%")
                      ->orWhere('sku', 'like', "%{$value}%");
                })
                ->take(8)
                ->get()
                ->map(fn($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'price' => $p->selling_price,
                    'stock' => $p->inventory->first()?->quantity ?? 0,
                    'image' => $p->image,
                ])
                ->toArray();
            $this->showSearchResults = true;
        } else {
            $this->searchResults = [];
            $this->showSearchResults = false;
        }
    }

    public function updatedCustomerSearch(string $value): void
    {
        if (strlen($value) >= 2) {
            $this->customerResults = Customer::where('is_active', true)
                ->where(function ($q) use ($value) {
                    $q->where('name', 'like', "%{$value}%")
                      ->orWhere('phone', 'like', "%{$value}%")
                      ->orWhere('email', 'like', "%{$value}%");
                })
                ->take(6)
                ->get(['id', 'name', 'phone', 'loyalty_points'])
                ->toArray();
            $this->showCustomerResults = true;
        } else {
            $this->customerResults = [];
            $this->showCustomerResults = false;
        }
    }

    public function addToCart(int $productId, ?int $variantId = null): void
    {
        $product = Product::find($productId);
        if (! $product) return;

        $cartKey = $variantId ? "p{$productId}v{$variantId}" : "p{$productId}";

        $branchId = Auth::user()?->branch_id;
        $stock = (float) Inventory::where('product_id', $productId)
            ->when($variantId, fn($q) => $q->where('product_variant_id', $variantId), fn($q) => $q->whereNull('product_variant_id'))
            ->where('branch_id', $branchId)
            ->value('quantity') ?? 0;

        if (isset($this->cart[$cartKey])) {
            $newQty = $this->cart[$cartKey]['qty'] + 1;
            if ($product->track_inventory && $newQty > $stock) {
                $this->dispatch('notify', type: 'error', message: __('Insufficient stock for :product', ['product' => $product->name]));
                return;
            }
            $this->cart[$cartKey]['qty'] = $newQty;
        } else {
            if ($product->track_inventory && $stock <= 0) {
                $this->dispatch('notify', type: 'error', message: __(':product is out of stock', ['product' => $product->name]));
                return;
            }
            $this->cart[$cartKey] = [
                'product_id' => $product->id,
                'variant_id' => $variantId,
                'name' => $product->name,
                'qty' => 1,
                'price' => (float) $product->selling_price,
                'cost' => (float) $product->cost_price,
                'tax_rate' => (float) $product->tax_rate,
                'stock' => $stock,
                'discount' => 0,
            ];
        }

        $this->recalculate();
        $this->productSearch = '';
        $this->searchResults = [];
        $this->showSearchResults = false;
    }

    public function removeFromCart(string $key): void
    {
        unset($this->cart[$key]);
        $this->recalculate();
    }

    public function updateQty(string $key, float $qty): void
    {
        if ($qty <= 0) {
            $this->removeFromCart($key);
            return;
        }
        $item = $this->cart[$key] ?? null;
        if (! $item) return;

        $product = Product::find($item['product_id']);
        if ($product && $product->track_inventory && $qty > $item['stock']) {
            $this->dispatch('notify', type: 'error', message: __('Cannot exceed available stock (:stock)', ['stock' => $item['stock']]));
            return;
        }
        $this->cart[$key]['qty'] = $qty;
        $this->recalculate();
    }

    public function updatePrice(string $key, float $price): void
    {
        $this->cart[$key]['price'] = max(0, $price);
        $this->recalculate();
    }

    public function updateItemDiscount(string $key, float $discount): void
    {
        $this->cart[$key]['discount'] = max(0, $discount);
        $this->recalculate();
    }

    public function selectCustomer(int $id): void
    {
        $customer = Customer::find($id);
        if ($customer) {
            $this->customerId = $customer->id;
            $this->customerSearch = $customer->name;
        }
        $this->customerResults = [];
        $this->showCustomerResults = false;
    }

    public function clearCustomer(): void
    {
        $this->customerId = null;
        $this->customerSearch = '';
    }

    public function applyCoupon(): void
    {
        $normalizedCode = strtoupper(trim($this->couponCode));

        if ($normalizedCode === '') return;

        $this->couponCode = $normalizedCode;

        $discount = Discount::where('code', $normalizedCode)->first();
        if (! $discount || ! $discount->isValid()) {
            $this->discountId = null;
            $this->recalculate();
            $this->couponMessage = __('Invalid or expired coupon code.');
            return;
        }

        $this->discountId = $discount->id;
        $this->recalculate();
        $this->couponMessage = $this->discountAmount > 0
            ? __('Coupon applied: :name', ['name' => $discount->name])
            : __('Coupon is valid, but no eligible items in the current cart match it yet.');
    }

    public function clearCoupon(): void
    {
        $this->couponCode = '';
        $this->discountId = null;
        $this->couponMessage = '';
        $this->recalculate();
    }

    public function getSubtotalProperty(): float
    {
        return (float) collect($this->cart)->sum(function ($item) {
            return ($item['qty'] * $item['price']) - ($item['discount'] ?? 0);
        });
    }

    public function getTaxAmountProperty(): float
    {
        return (float) collect($this->cart)->sum(function ($item) {
            $lineTotal = ($item['qty'] * $item['price']) - ($item['discount'] ?? 0);
            return $lineTotal * ($item['tax_rate'] / 100);
        });
    }

    public function getTotalProperty(): float
    {
        return max(0, $this->subtotal + $this->taxAmount - $this->discountAmount);
    }

    public function getChangeProperty(): float
    {
        $tendered = (float) $this->cashTendered;
        return max(0, $tendered - $this->total);
    }

    public function hasSecondaryCurrency(): bool
    {
        return $this->secondaryCurrencyCode !== null
            && $this->secondaryCurrencyCode !== ''
            && $this->secondaryCurrencyCode !== $this->currencyCode
            && $this->exchangeRate !== null
            && $this->exchangeRate > 0;
    }

    public function convertToSecondary(float $amount): ?float
    {
        if (! $this->hasSecondaryCurrency()) {
            return null;
        }

        return round($amount * $this->exchangeRate, 2);
    }

    public function formatMoney(float $amount, ?string $currencyCode = null, ?string $currencySymbol = null): string
    {
        $currencyCode ??= $this->currencyCode;
        $currencySymbol ??= $this->currencySymbol;
        $precision = $this->currencyPrecision($currencyCode);
        $formattedAmount = number_format($amount, $precision);

        return match ($currencySymbol) {
            '$' => '$' . $formattedAmount,
            default => $formattedAmount . ' ' . ($currencySymbol ?: $currencyCode),
        };
    }

    public function formatPrimaryMoney(float $amount): string
    {
        return $this->formatMoney($amount, $this->currencyCode, $this->currencySymbol);
    }

    public function formatSecondaryMoney(float $amount): ?string
    {
        $converted = $this->convertToSecondary($amount);

        if ($converted === null) {
            return null;
        }

        return $this->formatMoney($converted, $this->secondaryCurrencyCode, $this->secondaryCurrencySymbol);
    }

    public function formatDualMoney(float $amount): string
    {
        $formatted = $this->formatPrimaryMoney($amount);

        if (! $this->hasSecondaryCurrency()) {
            return $formatted;
        }

        return $formatted . ' / ' . $this->formatSecondaryMoney($amount);
    }

    private function currencyPrecision(?string $currencyCode): int
    {
        return match ($currencyCode) {
            'LBP' => 0,
            default => 2,
        };
    }

    private function recalculate(): void
    {
        $this->autoDiscountId = null;

        if ($this->discountId) {
            $discount = Discount::find($this->discountId);
            $this->discountAmount = $discount ? $discount->calculateDiscountForCart($this->cart) : 0;
            return;
        }

        $bestAutoDiscount = Discount::query()
            ->whereNull('code')
            ->where('is_active', true)
            ->get()
            ->filter(fn (Discount $discount) => $discount->isValid())
            ->map(function (Discount $discount): array {
                return [
                    'id' => $discount->id,
                    'amount' => $discount->calculateDiscountForCart($this->cart),
                ];
            })
            ->filter(fn (array $discount) => $discount['amount'] > 0)
            ->sortByDesc('amount')
            ->first();

        if ($bestAutoDiscount) {
            $this->autoDiscountId = $bestAutoDiscount['id'];
            $this->discountAmount = $bestAutoDiscount['amount'];
            return;
        }

        $this->discountAmount = 0;
    }

    public function openPaymentModal(): void
    {
        if (empty($this->cart)) {
            $this->dispatch('notify', type: 'error', message: __('Cart is empty'));
            return;
        }
        $this->cashTendered = (string) $this->total;
        $this->showPaymentModal = true;
    }

    public function processPayment(): void
    {
        if (empty($this->cart)) return;

        if ($this->paymentMethod === 'cash' && (float) $this->cashTendered < $this->total) {
            $this->dispatch('notify', type: 'error', message: __('Insufficient cash tendered'));
            return;
        }

        DB::transaction(function () {
            $branchId = Auth::user()?->branch_id;
            $appliedDiscountId = $this->discountId ?? $this->autoDiscountId;

            $sale = Sale::create([
                'branch_id' => $branchId,
                'user_id' => Auth::id(),
                'customer_id' => $this->customerId,
                'discount_id' => $appliedDiscountId,
                'status' => 'completed',
                'subtotal' => $this->subtotal,
                'discount_amount' => $this->discountAmount,
                'tax_amount' => $this->taxAmount,
                'total' => $this->total,
                'amount_paid' => $this->paymentMethod === 'cash' ? (float) $this->cashTendered : $this->total,
                'change_amount' => $this->paymentMethod === 'cash' ? $this->change : 0,
                'notes' => $this->notes,
            ]);

            foreach ($this->cart as $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['variant_id'],
                    'product_name' => $item['name'],
                    'quantity' => $item['qty'],
                    'unit_price' => $item['price'],
                    'cost_price' => $item['cost'],
                    'discount_amount' => $item['discount'] ?? 0,
                    'tax_rate' => $item['tax_rate'],
                    'tax_amount' => round((($item['qty'] * $item['price']) - ($item['discount'] ?? 0)) * ($item['tax_rate'] / 100), 2),
                    'total_price' => round(($item['qty'] * $item['price']) - ($item['discount'] ?? 0), 2),
                ]);

                // Deduct inventory
                $inventoryRecord = Inventory::firstOrCreate(
                    ['product_id' => $item['product_id'], 'product_variant_id' => $item['variant_id'], 'branch_id' => $branchId],
                    ['quantity' => 0]
                );
                $before = (float) $inventoryRecord->quantity;
                $inventoryRecord->decrement('quantity', $item['qty']);
                $after = $before - $item['qty'];

                InventoryMovement::create([
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['variant_id'],
                    'branch_id' => $branchId,
                    'user_id' => Auth::id(),
                    'type' => 'sale',
                    'quantity' => -$item['qty'],
                    'quantity_before' => $before,
                    'quantity_after' => $after,
                    'unit_cost' => $item['cost'],
                    'reference_type' => Sale::class,
                    'reference_id' => $sale->id,
                ]);
            }

            // Payment record
            Payment::create([
                'sale_id' => $sale->id,
                'method' => $this->paymentMethod,
                'amount' => $this->total,
                'tendered' => $this->paymentMethod === 'cash' ? (float) $this->cashTendered : $this->total,
                'change_amount' => $this->paymentMethod === 'cash' ? $this->change : 0,
                'status' => 'completed',
            ]);

            // Update discount usage
            if ($appliedDiscountId) {
                Discount::find($appliedDiscountId)?->increment('used_count');
            }

            // Update customer stats
            if ($this->customerId) {
                $customer = Customer::find($this->customerId);
                if ($customer) {
                    $loyaltyEnabled = AppSetting::getBool('enable_loyalty', false);
                    $loyaltyRate = max(0, AppSetting::getFloat('loyalty_rate', 1));
                    $points = $loyaltyEnabled ? (int) floor($this->total * $loyaltyRate) : 0;

                    $customer->increment('total_purchases', $this->total);

                    if ($points > 0) {
                        $customer->increment('loyalty_points', $points);
                    }

                    $sale->update(['loyalty_points_earned' => $points]);
                }
            }

            $this->completedSaleId = $sale->id;
        });

        $this->showPaymentModal = false;
        $this->showReceiptModal = true;
        $this->resetCart();
    }

    private function resetCart(): void
    {
        $this->cart = [];
        $this->customerId = null;
        $this->customerSearch = '';
        $this->couponCode = '';
        $this->discountId = null;
        $this->autoDiscountId = null;
        $this->discountAmount = 0;
        $this->couponMessage = '';
        $this->cashTendered = '';
        $this->notes = '';
        $this->paymentMethod = 'cash';
    }

    public function closeReceipt(): void
    {
        $this->showReceiptModal = false;
        $this->completedSaleId = null;
    }

    public function render()
    {
        $customer = $this->customerId ? Customer::find($this->customerId) : null;
        $completedSale = $this->completedSaleId
            ? Sale::with(['items.product', 'customer', 'cashier', 'payments'])->find($this->completedSaleId)
            : null;

        $categories = \App\Models\Category::where('is_active', true)->orderBy('sort_order')->get();
        $branchId = Auth::user()?->branch_id;
        $featuredProducts = Product::with(['inventory' => fn($q) => $q->where('branch_id', $branchId)->whereNull('product_variant_id')])
            ->where('is_active', true)
            ->orderBy('name')
            ->take(24)
            ->get();

        return view('livewire.pos.checkout', compact('customer', 'completedSale', 'categories', 'featuredProducts'))
            ->layout('layouts.app', ['title' => 'POS / Checkout']);
    }
}
