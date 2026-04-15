<div>
    {{-- Filters --}}
    <div class="flex flex-wrap items-center gap-3 mb-4">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="{{ __('Search product or SKU...') }}" class="input-field w-56" />
        <select wire:model.live="branch_id" class="input-field">
            <option value="">{{ __('All Branches') }}</option>
            @foreach($branches as $b)
                <option value="{{ $b->id }}">{{ $b->name }}</option>
            @endforeach
        </select>
        <select wire:model.live="category" class="input-field">
            <option value="">{{ __('All Categories') }}</option>
            @foreach($categories as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
            @endforeach
        </select>
        <select wire:model.live="stock" class="input-field">
            <option value="">{{ __('All Stock') }}</option>
            <option value="ok">{{ __('In Stock') }}</option>
            <option value="low">{{ __('Low Stock') }}</option>
            <option value="out">{{ __('Out of Stock') }}</option>
        </select>
    </div>

    @if(session('success'))
        <div class="alert-success mb-3">{{ session('success') }}</div>
    @endif

    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="table-header">
                <tr>
                    <th class="px-4 py-3 text-left">{{ __('Product') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Branch') }}</th>
                    <th class="px-4 py-3 text-center">{{ __('Qty') }}</th>
                    <th class="px-4 py-3 text-center">{{ __('Reorder Level') }}</th>
                    <th class="px-4 py-3 text-center">{{ __('Status') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($inventory as $inv)
                    @php
                        $reorderLevel = $inv->product?->min_stock_alert ?? 0;
                        $stockStatus = $inv->quantity <= 0 ? 'out' : ($inv->quantity <= $reorderLevel ? 'low' : 'ok');
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-900">{{ $inv->product->name }}</div>
                            <div class="text-xs text-gray-400">{{ $inv->product->sku }}</div>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $inv->branch->name }}</td>
                        <td class="px-4 py-3 text-center font-semibold
                            {{ $stockStatus === 'out' ? 'text-red-600' : ($stockStatus === 'low' ? 'text-amber-600' : 'text-green-600') }}">
                            {{ number_format($inv->quantity, 2) }}
                        </td>
                        <td class="px-4 py-3 text-center text-gray-500">{{ $reorderLevel }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($stockStatus === 'out')
                                <span class="badge-red">{{ __('Out of Stock') }}</span>
                            @elseif($stockStatus === 'low')
                                <span class="badge-amber">{{ __('Low Stock') }}</span>
                            @else
                                <span class="badge-green">{{ __('In Stock') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button wire:click="openAdjust({{ $inv->id }})" class="btn-icon-edit" title="{{ __('Adjust Stock') }}">
                                <x-pos-icon name="arrow-path" class="w-4 h-4" />
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-gray-400">{{ __('No inventory records found.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t">{{ $inventory->links() }}</div>
    </div>

    {{-- Adjust Modal --}}
    @if($showAdjustModal)
    <div class="modal-overlay" wire:click.self="$set('showAdjustModal',false)">
        <div class="modal-box max-w-sm w-full">
            <h3 class="modal-title">{{ __('Adjust Stock') }}</h3>
            <div class="space-y-4 mt-4">
                <div>
                    <label class="form-label">{{ __('Adjustment Type') }}</label>
                    <select wire:model="adjustType" class="input-field w-full">
                        <option value="add">{{ __('Add Stock') }}</option>
                        <option value="subtract">{{ __('Remove Stock') }}</option>
                        <option value="set">{{ __('Set Exact Quantity') }}</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">{{ __('Quantity') }} *</label>
                    <input wire:model="adjustQty" type="number" min="0" step="0.01" class="input-field w-full" />
                    @error('adjustQty') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">{{ __('Note') }}</label>
                    <textarea wire:model="adjustNote" class="input-field w-full" rows="2" placeholder="{{ __('Reason for adjustment...') }}"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button wire:click="$set('showAdjustModal',false)" class="btn-secondary">{{ __('Cancel') }}</button>
                <button wire:click="saveAdjustment" class="btn-primary">{{ __('Save') }}</button>
            </div>
        </div>
    </div>
    @endif
</div>
