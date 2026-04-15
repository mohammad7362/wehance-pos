<div>
    <div class="flex items-center justify-between mb-4">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search discounts..." class="input-field w-64" />
        <button wire:click="openCreate" class="btn-primary flex items-center gap-2">
            <x-pos-icon name="plus" class="w-4 h-4" /> New Discount
        </button>
    </div>

    @if(session('success'))
        <div class="alert-success mb-3">{{ session('success') }}</div>
    @endif

    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="table-header">
                <tr>
                    <th class="px-4 py-3 text-left">Name</th>
                    <th class="px-4 py-3 text-left">Code</th>
                    <th class="px-4 py-3 text-center">Type</th>
                    <th class="px-4 py-3 text-center">Value</th>
                    <th class="px-4 py-3 text-center">Expires</th>
                    <th class="px-4 py-3 text-center">Usage</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($discounts as $d)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium">{{ $d->name }}</td>
                        <td class="px-4 py-3">
                            @if($d->code)
                                <span class="font-mono bg-gray-100 px-2 py-0.5 rounded text-xs">{{ $d->code }}</span>
                            @else
                                <span class="text-gray-400">Auto</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="badge-{{ $d->type === 'percentage' ? 'blue' : 'purple' }}">
                                {{ $d->type === 'percentage' ? '%' : '$' }} {{ ucfirst($d->type) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center font-semibold">
                            {{ $d->type === 'percentage' ? $d->value . '%' : '$' . number_format($d->value, 2) }}
                        </td>
                        <td class="px-4 py-3 text-center text-gray-500">{{ $d->expires_at?->format('M d, Y') ?? '—' }}</td>
                        <td class="px-4 py-3 text-center text-gray-500">
                            {{ $d->used_count ?? 0 }}{{ $d->usage_limit ? ' / ' . $d->usage_limit : '' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <button wire:click="toggleActive({{ $d->id }})">
                                <span class="badge-{{ $d->is_active ? 'green' : 'red' }} cursor-pointer">
                                    {{ $d->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </button>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button wire:click="openEdit({{ $d->id }})" class="btn-icon-edit">
                                    <x-pos-icon name="pencil" class="w-4 h-4" />
                                </button>
                                <button wire:click="confirmDelete({{ $d->id }})" class="btn-icon-delete">
                                    <x-pos-icon name="trash" class="w-4 h-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-12 text-center text-gray-400">No discounts found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t">{{ $discounts->links() }}</div>
    </div>

    @if($showModal)
    <div class="modal-overlay" wire:click.self="$set('showModal',false)">
        <div class="modal-box max-w-lg w-full">
            <h3 class="modal-title">{{ $editingId ? 'Edit Discount' : 'New Discount' }}</h3>
            <div class="grid grid-cols-2 gap-4 mt-4">
                <div class="col-span-2">
                    <label class="form-label">Name *</label>
                    <input wire:model="name" type="text" class="input-field w-full" />
                    @error('name') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Coupon Code</label>
                    <input wire:model="code" type="text" class="input-field w-full uppercase" placeholder="Leave blank for auto" />
                </div>
                <div>
                    <label class="form-label">Type *</label>
                    <select wire:model="type" class="input-field w-full">
                        <option value="percentage">Percentage (%)</option>
                        <option value="fixed">Fixed Amount ($)</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Value *</label>
                    <input wire:model="value" type="number" min="0" step="0.01" class="input-field w-full" />
                    @error('value') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Min Purchase</label>
                    <input wire:model="min_purchase" type="number" min="0" step="0.01" class="input-field w-full" />
                </div>
                <div>
                    <label class="form-label">Max Discount</label>
                    <input wire:model="max_discount" type="number" min="0" step="0.01" class="input-field w-full" />
                </div>
                <div>
                    <label class="form-label">Usage Limit</label>
                    <input wire:model="usage_limit" type="number" min="1" class="input-field w-full" placeholder="Unlimited" />
                </div>
                <div>
                    <label class="form-label">Applies To</label>
                    <select wire:model="applies_to" class="input-field w-full">
                        <option value="all">Entire Sale</option>
                        <option value="category">Specific Categories</option>
                        <option value="product">Specific Products</option>
                    </select>
                    @error('applies_to') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                @if($applies_to !== 'all')
                <div class="col-span-2">
                    <label class="form-label">Eligible {{ $applies_to === 'category' ? 'Categories' : 'Products' }}</label>
                    <div class="max-h-56 overflow-y-auto rounded-lg border border-gray-200 p-3 space-y-2">
                        @php($options = $applies_to === 'category' ? $categories : $products)
                        @forelse($options as $option)
                            <label class="flex items-center gap-3 text-sm text-gray-700">
                                <input wire:model="selectedItems" type="checkbox" value="{{ $option->id }}" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                                <span>
                                    {{ $option->name }}
                                    @if($applies_to === 'product' && $option->sku)
                                        <span class="text-xs text-gray-400">({{ $option->sku }})</span>
                                    @endif
                                </span>
                            </label>
                        @empty
                            <p class="text-sm text-gray-400">No active {{ $applies_to === 'category' ? 'categories' : 'products' }} found.</p>
                        @endforelse
                    </div>
                    @error('selectedItems') <p class="form-error">{{ $message }}</p> @enderror
                    @error('selectedItems.*') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                @endif
                <div>
                    <label class="form-label">Starts At</label>
                    <input wire:model="starts_at" type="date" class="input-field w-full" />
                </div>
                <div>
                    <label class="form-label">Expires At</label>
                    <input wire:model="expires_at" type="date" class="input-field w-full" />
                    @error('expires_at') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div class="flex items-center gap-2">
                    <input wire:model="is_active" type="checkbox" id="disc_active" class="w-4 h-4" />
                    <label for="disc_active" class="text-sm">Active</label>
                </div>
            </div>
            <div class="modal-footer">
                <button wire:click="$set('showModal',false)" class="btn-secondary">Cancel</button>
                <button wire:click="save" class="btn-primary">Save</button>
            </div>
        </div>
    </div>
    @endif

    @if($showDeleteModal)
    <div class="modal-overlay">
        <div class="modal-box max-w-sm w-full text-center">
            <x-pos-icon name="exclamation-triangle" class="w-12 h-12 text-red-500 mx-auto mb-3" />
            <h3 class="text-lg font-semibold">Delete Discount?</h3>
            <div class="modal-footer justify-center">
                <button wire:click="$set('showDeleteModal',false)" class="btn-secondary">Cancel</button>
                <button wire:click="delete" class="btn-danger">Delete</button>
            </div>
        </div>
    </div>
    @endif
</div>
