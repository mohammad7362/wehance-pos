@php
    $isRtl = app()->getLocale() === 'ar';
@endphp
<div wire:key="pos-checkout" class="space-y-0 {{ $isRtl ? 'text-right' : '' }}" x-data="{ posTab: 'products' }">
{{-- Mobile Tab Bar --}}
<div class="flex lg:hidden bg-white border border-slate-200 rounded-xl mb-3 overflow-hidden">
    <button @click="posTab = 'products'"
        :class="posTab === 'products' ? 'bg-blue-600 text-white' : 'text-slate-500 hover:bg-slate-50'"
        class="flex-1 py-3 text-sm font-semibold transition-colors">
        {{ __('Products') }}
    </button>
    <button @click="posTab = 'cart'"
        :class="posTab === 'cart' ? 'bg-blue-600 text-white' : 'text-slate-500 hover:bg-slate-50'"
        class="flex-1 py-3 text-sm font-semibold transition-colors">
        {{ __('Cart') }} ({{ count($cart) }})
    </button>
</div>
<div class="flex gap-4 lg:h-[calc(100vh-8rem)]">

    {{-- LEFT: Product Search & Grid --}}
    <div class="flex-1 flex flex-col gap-4 min-w-0" :class="posTab !== 'products' ? 'hidden lg:flex' : ''">

        {{-- Search Bar --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4">
            <div class="flex gap-3">
                {{-- Barcode Input --}}
                <div class="relative">
                    <x-pos-icon name="barcode" class="w-4 h-4 absolute {{ $isRtl ? 'right-3' : 'left-3' }} top-1/2 -translate-y-1/2 text-slate-400" />
                    <input wire:model.live="barcodeInput" type="text" placeholder="{{ __('Scan barcode...') }}"
                        class="{{ $isRtl ? 'pr-9 pl-4' : 'pl-9 pr-4' }} py-2.5 text-sm border border-slate-200 rounded-xl w-48 focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400"
                        autofocus>
                </div>
                {{-- Product Search --}}
                <div class="relative flex-1">
                    <x-pos-icon name="search" class="w-4 h-4 absolute {{ $isRtl ? 'right-3' : 'left-3' }} top-1/2 -translate-y-1/2 text-slate-400" />
                    <input wire:model.live.debounce.250ms="productSearch" type="text" placeholder="{{ __('Search products by name, SKU, barcode...') }}"
                        class="w-full {{ $isRtl ? 'pr-9 pl-4' : 'pl-9 pr-4' }} py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400">
                    {{-- Search Dropdown --}}
                    @if($showSearchResults && count($searchResults) > 0)
                    <div class="absolute top-full left-0 right-0 bg-white border border-slate-200 rounded-xl shadow-lg z-20 mt-1 py-1 max-h-64 overflow-y-auto">
                        @foreach($searchResults as $result)
                        <button wire:click="addToCart({{ $result['id'] }})"
                            class="flex items-center gap-3 w-full px-4 py-2.5 hover:bg-blue-50 transition-colors text-left">
                            <div class="w-8 h-8 bg-slate-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <x-pos-icon name="cube" class="w-4 h-4 text-slate-400" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-slate-800 truncate">{{ $result['name'] }}</p>
                                <p class="text-xs text-slate-500">{{ __('Stock') }}: {{ number_format($result['stock'], 0) }}</p>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <span class="text-sm font-semibold text-blue-600 block">{{ $this->formatPrimaryMoney((float) $result['price']) }}</span>
                                @if($this->hasSecondaryCurrency())
                                <span class="text-xs text-slate-400 block">{{ $this->formatSecondaryMoney((float) $result['price']) }}</span>
                                @endif
                            </div>
                        </button>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Product Grid --}}
        <div class="flex-1 overflow-y-auto">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
                @foreach($featuredProducts as $product)
                @php $stock = $product->inventory->first()?->quantity ?? 0; @endphp
                <button wire:click="addToCart({{ $product->id }})"
                    class="bg-white border border-slate-100 rounded-2xl p-3 text-left shadow-sm hover:shadow-md hover:border-blue-200 transition-all
                           {{ $stock <= 0 && $product->track_inventory ? 'opacity-50 cursor-not-allowed' : 'hover:bg-blue-50' }}"
                    {{ $stock <= 0 && $product->track_inventory ? 'disabled' : '' }}>
                    <div class="w-full aspect-square bg-slate-100 rounded-xl flex items-center justify-center mb-2 overflow-hidden">
                        @if($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}" class="w-full h-full object-cover rounded-xl">
                        @else
                        <x-pos-icon name="cube" class="w-8 h-8 text-slate-300" />
                        @endif
                    </div>
                    <p class="text-xs font-semibold text-slate-800 truncate leading-tight">{{ $product->name }}</p>
                    <p class="text-sm font-bold text-blue-600 mt-1">{{ $this->formatPrimaryMoney((float) $product->selling_price) }}</p>
                    @if($this->hasSecondaryCurrency())
                    <p class="text-xs text-slate-400 mt-0.5">{{ $this->formatSecondaryMoney((float) $product->selling_price) }}</p>
                    @endif
                    <p class="text-xs text-slate-400 mt-0.5">
                        {{ $product->track_inventory ? __('Stock') . ': ' . number_format($stock, 0) : __('Unlimited') }}
                    </p>
                </button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- RIGHT: Cart --}}
    <div class="w-full lg:w-96 flex flex-col gap-3 lg:flex-shrink-0" :class="posTab !== 'cart' ? 'hidden lg:flex' : ''">

        {{-- Customer --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4">
            <div class="flex items-center gap-2 mb-2">
                <x-pos-icon name="users" class="w-4 h-4 text-slate-400" />
                <span class="text-sm font-medium text-slate-700">{{ __('Customer') }}</span>
                @if($customerId)
                <button wire:click="clearCustomer" class="{{ $isRtl ? 'mr-auto' : 'ml-auto' }} text-xs text-red-500 hover:text-red-700">{{ __('Remove') }}</button>
                @endif
            </div>
            @if($customerId && $customer)
            <div class="flex items-center gap-2 bg-blue-50 rounded-lg p-2">
                <div class="w-7 h-7 bg-blue-600 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-800">{{ $customer->name }}</p>
                    <p class="text-xs text-slate-500">{{ $customer->loyalty_points }} {{ __('pts') }}</p>
                </div>
            </div>
            @else
            <div class="relative">
                <input wire:model.live.debounce.250ms="customerSearch" type="text" placeholder="{{ __('Search customer...') }}"
                    class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                @if($showCustomerResults && count($customerResults) > 0)
                <div class="absolute top-full left-0 right-0 bg-white border border-slate-200 rounded-xl shadow-lg z-20 mt-1 py-1">
                    @foreach($customerResults as $c)
                    <button wire:click="selectCustomer({{ $c['id'] }})"
                        class="flex items-center gap-2 w-full px-3 py-2 hover:bg-blue-50 transition-colors text-left">
                        <div class="w-7 h-7 bg-slate-200 rounded-full flex items-center justify-center text-slate-600 text-xs font-bold flex-shrink-0">
                            {{ strtoupper(substr($c['name'], 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-800">{{ $c['name'] }}</p>
                            <p class="text-xs text-slate-500">{{ $c['phone'] }}</p>
                        </div>
                    </button>
                    @endforeach
                </div>
                @endif
            </div>
            @endif
        </div>

        {{-- Cart Items --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 flex-1 flex flex-col overflow-hidden">
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
                <span class="font-semibold text-slate-800">{{ __('Cart') }}</span>
                <span class="text-xs text-slate-500">{{ count($cart) }} {{ count($cart) === 1 ? __('item') : __('items') }}</span>
            </div>
            <div class="flex-1 overflow-y-auto px-4 py-3 space-y-3">
                @forelse($cart as $key => $item)
                <div class="flex items-start gap-3 pb-3 border-b border-slate-100 last:border-0 last:pb-0">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-800 truncate">{{ $item['name'] }}</p>
                        <p class="text-xs text-slate-400">{{ $this->formatDualMoney((float) $item['price']) }} {{ __('each') }}</p>
                        <div class="flex items-center gap-2 mt-1.5">
                            {{-- Qty Controls --}}
                            <button wire:click="updateQty('{{ $key }}', {{ $item['qty'] - 1 }})"
                                class="w-6 h-6 flex items-center justify-center bg-slate-100 hover:bg-slate-200 rounded-md text-slate-600 font-bold text-sm transition-colors">−</button>
                            <input wire:change="updateQty('{{ $key }}', $event.target.value)" type="number" value="{{ $item['qty'] }}" min="0" step="1"
                                class="w-12 text-center text-sm border border-slate-200 rounded-md py-0.5 focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <button wire:click="updateQty('{{ $key }}', {{ $item['qty'] + 1 }})"
                                class="w-6 h-6 flex items-center justify-center bg-slate-100 hover:bg-slate-200 rounded-md text-slate-600 font-bold text-sm transition-colors">+</button>
                        </div>
                    </div>
                    <div class="text-right flex flex-col items-end gap-1.5">
                        <span class="text-sm font-semibold text-slate-800">{{ $this->formatDualMoney((float) ($item['qty'] * $item['price'])) }}</span>
                        <button wire:click="removeFromCart('{{ $key }}')" class="text-slate-300 hover:text-red-500 transition-colors">
                            <x-pos-icon name="x-mark" class="w-4 h-4" />
                        </button>
                    </div>
                </div>
                @empty
                <div class="flex flex-col items-center justify-center py-10 text-slate-300">
                    <x-pos-icon name="shopping-cart" class="w-10 h-10 mb-2" />
                    <p class="text-sm">{{ __('Cart is empty') }}</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Coupon --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 px-4 py-3">
            <div class="flex gap-2">
                <input wire:model="couponCode" type="text" placeholder="{{ __('Coupon code') }}"
                    class="flex-1 px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                <button wire:click="applyCoupon" class="px-3 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium rounded-lg transition-colors">{{ __('Apply') }}</button>
                @if($discountId)
                <button wire:click="clearCoupon" class="px-2.5 py-2 text-slate-400 hover:text-red-500 rounded-lg hover:bg-red-50 transition-colors">
                    <x-pos-icon name="x-mark" class="w-4 h-4" />
                </button>
                @endif
            </div>
            @if($couponMessage)
            <p class="text-xs mt-2 {{ $discountId ? 'text-green-600' : 'text-red-500' }}">{{ $couponMessage }}</p>
            @elseif($autoDiscountId)
            <p class="text-xs mt-2 text-green-600">{{ __('Automatic discount applied to eligible cart items.') }}</p>
            @endif
        </div>

        {{-- Totals --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 px-4 py-4 space-y-2">
            <div class="flex justify-between text-sm text-slate-600">
                <span>{{ __('Subtotal') }}</span><span>{{ $this->formatPrimaryMoney($this->subtotal) }}</span>
            </div>
            @if($this->hasSecondaryCurrency())
            <div class="flex justify-between text-xs text-slate-400">
                <span>{{ $secondaryCurrencyCode }}</span><span>{{ $this->formatSecondaryMoney($this->subtotal) }}</span>
            </div>
            @endif
            @if($discountAmount > 0)
            <div class="flex justify-between text-sm text-green-600">
                <span>{{ __('Discount') }}</span><span>-{{ $this->formatPrimaryMoney($discountAmount) }}</span>
            </div>
            @if($this->hasSecondaryCurrency())
            <div class="flex justify-between text-xs text-green-500">
                <span>{{ $secondaryCurrencyCode }}</span><span>-{{ $this->formatSecondaryMoney($discountAmount) }}</span>
            </div>
            @endif
            @endif
            <div class="flex justify-between text-sm text-slate-600">
                <span>{{ __('Tax') }}</span><span>{{ $this->formatPrimaryMoney($this->taxAmount) }}</span>
            </div>
            @if($this->hasSecondaryCurrency())
            <div class="flex justify-between text-xs text-slate-400">
                <span>{{ $secondaryCurrencyCode }}</span><span>{{ $this->formatSecondaryMoney($this->taxAmount) }}</span>
            </div>
            @endif
            <div class="flex justify-between text-lg font-bold text-slate-800 pt-2 border-t border-slate-100">
                <span>{{ __('Total') }}</span><span>{{ $this->formatPrimaryMoney($this->total) }}</span>
            </div>
            @if($this->hasSecondaryCurrency())
            <div class="flex justify-between text-sm font-medium text-slate-500">
                <span>{{ $secondaryCurrencyCode }}</span><span>{{ $this->formatSecondaryMoney($this->total) }}</span>
            </div>
            @endif
        </div>

        {{-- Charge Button --}}
        <button wire:click="openPaymentModal"
            class="w-full py-4 bg-blue-600 hover:bg-blue-500 disabled:opacity-40 disabled:cursor-not-allowed text-white text-lg font-bold rounded-2xl shadow-lg shadow-blue-600/30 transition-colors"
            {{ empty($cart) ? 'disabled' : '' }}>
            {{ __('Charge :amount', ['amount' => $this->formatPrimaryMoney($this->total)]) }}
        </button>
    </div>
</div>

<div>
    {{-- Payment Modal --}}
    @if($showPaymentModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md" wire:click.stop>
            <div class="flex items-center justify-between p-6 border-b border-slate-100">
                <h3 class="text-lg font-semibold text-slate-800">{{ __('Payment') }}</h3>
                <button wire:click="$set('showPaymentModal', false)" class="p-2 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100">
                    <x-pos-icon name="x-mark" class="w-5 h-5" />
                </button>
            </div>
            <div class="p-6 space-y-5">
                {{-- Amount Due --}}
                <div class="bg-blue-50 rounded-xl p-4 text-center">
                    <p class="text-sm text-blue-600 font-medium">{{ __('Amount Due') }}</p>
                    <p class="text-4xl font-bold text-blue-700 mt-1">{{ $this->formatPrimaryMoney($this->total) }}</p>
                    @if($this->hasSecondaryCurrency())
                    <p class="text-sm text-blue-500 mt-2">{{ $this->formatSecondaryMoney($this->total) }}</p>
                    @endif
                </div>

                {{-- Method --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('Payment Method') }}</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button wire:click="$set('paymentMethod', 'cash')"
                            class="flex items-center justify-center gap-2 py-3 rounded-xl border-2 transition-all font-medium text-sm
                                   {{ $paymentMethod === 'cash' ? 'border-blue-600 bg-blue-50 text-blue-700' : 'border-slate-200 text-slate-600 hover:border-slate-300' }}">
                            <x-pos-icon name="banknotes" class="w-5 h-5" /> {{ __('Cash') }}
                        </button>
                        <button wire:click="$set('paymentMethod', 'card')"
                            class="flex items-center justify-center gap-2 py-3 rounded-xl border-2 transition-all font-medium text-sm
                                   {{ $paymentMethod === 'card' ? 'border-blue-600 bg-blue-50 text-blue-700' : 'border-slate-200 text-slate-600 hover:border-slate-300' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            {{ __('Card') }}
                        </button>
                    </div>
                </div>

                {{-- Cash tendered --}}
                @if($paymentMethod === 'cash')
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">{{ __('Cash Tendered') }}</label>
                    <div class="relative">
                        <span class="absolute {{ $isRtl ? 'right-3' : 'left-3' }} top-1/2 -translate-y-1/2 text-slate-400 text-lg font-medium">{{ $currencySymbol }}</span>
                        <input wire:model.live="cashTendered" type="number" step="0.01" min="{{ $this->total }}"
                            class="w-full {{ $isRtl ? 'pr-7 pl-4' : 'pl-7 pr-4' }} py-3 text-xl font-bold border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400">
                    </div>
                    {{-- Quick amounts --}}
                    <div class="flex gap-2 mt-2">
                        @foreach([ceil($this->total), ceil($this->total/5)*5, ceil($this->total/10)*10, ceil($this->total/20)*20] as $amount)
                        @if($amount >= $this->total)
                        <button wire:click="$set('cashTendered', '{{ $amount }}')"
                            class="flex-1 py-1.5 text-sm border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors font-medium">
                            {{ $this->formatDualMoney((float) $amount) }}
                        </button>
                        @endif
                        @endforeach
                    </div>
                    @if((float)$cashTendered >= $this->total)
                    <div class="bg-green-50 border border-green-200 rounded-xl p-3 mt-3 text-center">
                        <p class="text-sm text-green-600">{{ __('Change') }}</p>
                        <p class="text-2xl font-bold text-green-700">{{ $this->formatPrimaryMoney($this->change) }}</p>
                        @if($this->hasSecondaryCurrency())
                        <p class="text-sm text-green-600 mt-1">{{ $this->formatSecondaryMoney($this->change) }}</p>
                        @endif
                    </div>
                    @endif
                </div>
                @endif

                {{-- Notes --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">{{ __('Notes (optional)') }}</label>
                    <input wire:model="notes" type="text" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/30" placeholder="{{ __('Order notes...') }}">
                </div>

                <button wire:click="processPayment"
                    class="w-full py-3.5 bg-green-600 hover:bg-green-500 text-white text-lg font-bold rounded-xl shadow-lg shadow-green-600/30 transition-colors">
                    {{ __('Complete Sale') }}
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Receipt Modal --}}
    @if($showReceiptModal && $completedSale)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm" wire:click.stop>
            <div class="flex items-center justify-between p-5 border-b border-slate-100">
                <h3 class="font-semibold text-slate-800">{{ __('Sale Complete!') }}</h3>
                <div class="flex gap-2">
                    <button onclick="window.print()" class="p-2 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100" title="{{ __('Print receipt') }}">
                        <x-pos-icon name="printer" class="w-5 h-5" />
                    </button>
                    <button wire:click="closeReceipt" class="p-2 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100">
                        <x-pos-icon name="x-mark" class="w-5 h-5" />
                    </button>
                </div>
            </div>
            <div class="p-5 print-only" id="receipt">
                <div class="text-center mb-4">
                    @php $receiptLogo = \App\Models\AppSetting::getValue('receipt_logo'); @endphp
                    @if($receiptLogo)
                        <img src="{{ asset('storage/' . $receiptLogo) }}" alt="Logo" class="mx-auto mb-2 max-h-16 object-contain" />
                    @endif
                    <p class="font-bold text-lg">{{ config('app.display_name', 'WehancePOS') }}</p>
                    <p class="text-xs text-slate-500">{{ auth()->user()->branch?->name }}</p>
                    <p class="text-xs text-slate-500">{{ $completedSale->created_at->locale(app()->getLocale())->translatedFormat('d M Y h:i A') }}</p>
                    <p class="text-xs font-mono text-slate-600 mt-1">{{ $completedSale->reference_no }}</p>
                </div>
                <div class="divide-y divide-slate-100 text-sm mb-4">
                    @foreach($completedSale->items as $item)
                    <div class="flex justify-between py-1.5">
                        <div>
                            <p class="font-medium">{{ $item->product_name }}</p>
                            <p class="text-xs text-slate-500">{{ number_format($item->quantity, 0) }} × {{ $this->formatDualMoney((float) $item->unit_price) }}</p>
                        </div>
                        <span class="font-medium">{{ $this->formatDualMoney((float) $item->total_price) }}</span>
                    </div>
                    @endforeach
                </div>
                <div class="bg-slate-50 rounded-lg p-3 space-y-1 text-sm">
                    <div class="flex justify-between"><span class="text-slate-500">{{ __('Subtotal') }}</span><span>{{ $this->formatPrimaryMoney((float) $completedSale->subtotal) }}</span></div>
                    @if($completedSale->discount_amount > 0)
                    <div class="flex justify-between text-green-600"><span>{{ __('Discount') }}</span><span>-{{ $this->formatPrimaryMoney((float) $completedSale->discount_amount) }}</span></div>
                    @endif
                    <div class="flex justify-between"><span class="text-slate-500">{{ __('Tax') }}</span><span>{{ $this->formatPrimaryMoney((float) $completedSale->tax_amount) }}</span></div>
                    <div class="flex justify-between font-bold text-base pt-1 border-t border-slate-200">
                        <span>{{ __('Total') }}</span><span>{{ $this->formatPrimaryMoney((float) $completedSale->total) }}</span>
                    </div>
                    @if($this->hasSecondaryCurrency())
                    <div class="flex justify-between text-slate-500"><span>{{ $secondaryCurrencyCode }}</span><span>{{ $this->formatSecondaryMoney((float) $completedSale->total) }}</span></div>
                    @endif
                    @foreach($completedSale->payments as $p)
                    <div class="flex justify-between text-slate-500"><span>{{ __(ucfirst($p->method)) }}</span><span>{{ $this->formatPrimaryMoney((float) $p->amount) }}</span></div>
                    @if($p->change_amount > 0)
                    <div class="flex justify-between text-green-600 font-medium"><span>{{ __('Change') }}</span><span>{{ $this->formatPrimaryMoney((float) $p->change_amount) }}</span></div>
                    @endif
                    @endforeach
                </div>
                @if($completedSale->customer && $completedSale->loyalty_points_earned > 0)
                <p class="text-center text-xs text-blue-600 mt-3">{{ __(':points loyalty points earned', ['points' => '+' . $completedSale->loyalty_points_earned]) }}</p>
                @endif
                <p class="text-center text-xs text-slate-400 mt-3">{{ auth()->user()->branch?->receipt_footer }}</p>
            </div>
            <div class="px-5 pb-5">
                <button wire:click="closeReceipt"
                    class="w-full py-3 bg-blue-600 hover:bg-blue-500 text-white font-semibold rounded-xl transition-colors">
                    {{ __('New Sale') }}
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Toast notifications --}}
    <div x-data="{ notifications: [] }"
        @notify.window="
            const n = { id: Date.now(), type: $event.detail[0]?.type || $event.detail?.type, message: $event.detail[0]?.message || $event.detail?.message };
            notifications.push(n);
            setTimeout(() => notifications = notifications.filter(x => x.id !== n.id), 3000);
        "
        class="fixed bottom-4 {{ $isRtl ? 'left-4' : 'right-4' }} z-50 space-y-2 pointer-events-none">
        <template x-for="n in notifications" :key="n.id">
            <div x-show="true" x-transition
                :class="n.type === 'error' ? 'bg-red-600' : 'bg-green-600'"
                class="flex items-center gap-2 px-4 py-3 rounded-xl text-white text-sm font-medium shadow-lg pointer-events-auto">
                <span x-text="n.message"></span>
            </div>
        </template>
    </div>
</div>
</div>
