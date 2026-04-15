<div>
    <div class="mb-4">
        <a href="{{ route('purchases.index') }}" class="btn-secondary inline-flex items-center gap-2">
            <x-pos-icon name="arrow-left" class="w-4 h-4" /> Back
        </a>
    </div>

    @if(session('error'))
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Header form --}}
        <div class="lg:col-span-3 card p-6">
            <h2 class="text-lg font-bold mb-4">{{ $isEdit ? 'Edit Purchase Order' : 'New Purchase Order' }}</h2>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="form-label">Supplier *</label>
                    <select wire:model="supplier_id" class="input-field w-full" @disabled($this->isReadOnly)>
                        <option value="">Select supplier...</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                    @error('supplier_id') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Branch *</label>
                    <select wire:model="branch_id" class="input-field w-full" @disabled($this->isReadOnly)>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Order Date *</label>
                    <input wire:model="ordered_at" type="date" class="input-field w-full" @disabled($this->isReadOnly) />
                </div>
                <div>
                    <label class="form-label">Expected Date</label>
                    <input wire:model="expected_at" type="date" class="input-field w-full" @disabled($this->isReadOnly) />
                </div>
            </div>
            <div class="mt-3">
                <label class="form-label">Notes</label>
                <textarea wire:model="notes" class="input-field w-full" rows="2" @disabled($this->isReadOnly)></textarea>
            </div>
        </div>

        {{-- Product search + items --}}
        <div class="lg:col-span-3 card p-6">
            <h3 class="font-semibold mb-3">Items</h3>

            {{-- Search --}}
            <div class="relative mb-4">
                <input wire:model.live.debounce.300ms="productSearch"
                    wire:keydown.enter="searchProducts"
                    type="text" placeholder="Search product to add..."
                    class="input-field w-full pr-10"
                    @disabled($this->isReadOnly) />
                @if(!$this->isReadOnly && count($productResults))
                <div class="absolute z-20 mt-1 w-full bg-white rounded-lg shadow-xl border max-h-60 overflow-auto">
                    @foreach($productResults as $pr)
                        <button wire:click="addProduct({{ $pr['id'] }})" class="w-full text-left px-4 py-2 hover:bg-gray-50 flex justify-between">
                            <span>{{ $pr['name'] }} <span class="text-gray-400 text-xs">({{ $pr['sku'] }})</span></span>
                            <span class="text-gray-500 text-sm">${{ number_format($pr['cost_price'] ?? 0, 2) }}</span>
                        </button>
                    @endforeach
                </div>
                @endif
            </div>

            @error('items') <p class="form-error mb-2">{{ $message }}</p> @enderror

            <table class="w-full text-sm">
                <thead class="table-header">
                    <tr>
                        <th class="px-3 py-2 text-left">Product</th>
                        <th class="px-3 py-2 text-center w-24">Qty</th>
                        <th class="px-3 py-2 text-center w-28">Unit Cost</th>
                        <th class="px-3 py-2 text-center w-28">Selling Price</th>
                        <th class="px-3 py-2 text-right w-28">Subtotal</th>
                        <th class="px-3 py-2 w-10"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($items as $i => $item)
                        <tr>
                            <td class="px-3 py-2 font-medium">{{ $item['product_name'] }}</td>
                            <td class="px-3 py-2">
                                <input wire:model.lazy="items.{{ $i }}.quantity" type="number" min="1" class="input-field text-center w-full" @disabled($this->isReadOnly) />
                            </td>
                            <td class="px-3 py-2">
                                <input wire:model.lazy="items.{{ $i }}.unit_cost" type="number" min="0" step="0.01" class="input-field text-right w-full" @disabled($this->isReadOnly) />
                            </td>
                            <td class="px-3 py-2">
                                <input wire:model.lazy="items.{{ $i }}.selling_price" type="number" min="0" step="0.01" class="input-field text-center w-full" @disabled($this->isReadOnly) />
                            </td>
                            <td class="px-3 py-2 text-right">${{ number_format(($item['quantity'] ?? 0) * ($item['unit_cost'] ?? 0), 2) }}</td>
                            <td class="px-3 py-2">
                                <button wire:click="removeItem({{ $i }})" class="text-red-400 hover:text-red-600" @disabled($this->isReadOnly)>
                                    <x-pos-icon name="x-mark" class="w-4 h-4" />
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-3 py-8 text-center text-gray-400">No items added yet.</td></tr>
                    @endforelse
                </tbody>
                <tfoot class="border-t-2 font-semibold">
                    <tr>
                        <td colspan="4" class="px-3 py-2 text-right">Subtotal:</td>
                        <td class="px-3 py-2 text-right">${{ number_format($this->subtotal, 2) }}</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="4" class="px-3 py-2 text-right">Tax:</td>
                        <td class="px-3 py-2 text-right">${{ number_format($this->taxTotal, 2) }}</td>
                        <td></td>
                    </tr>
                    <tr class="text-lg">
                        <td colspan="4" class="px-3 py-2 text-right font-bold">Total:</td>
                        <td class="px-3 py-2 text-right font-bold text-primary">${{ number_format($this->total, 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Actions --}}
        <div class="lg:col-span-3 flex items-center justify-end gap-3">
            @if($isEdit && $po?->status === 'received')
                <p class="text-sm text-amber-700">Received purchase orders are read-only to preserve inventory history.</p>
            @else
            <button wire:click="saveDraft" class="btn-secondary">Save Draft</button>
            <button wire:click="submitOrder" class="btn-primary">Submit Order</button>
            @if($isEdit && $po?->status === 'sent')
                <button wire:click="receiveOrder" wire:confirm="Mark this order as received and update inventory?" class="btn-success">
                    Mark as Received
                </button>
            @endif
            @endif
        </div>
    </div>
</div>
