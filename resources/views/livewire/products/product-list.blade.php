<div>
    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <h2 class="text-2xl font-bold text-slate-800">Products</h2>
        @can('create products')
        <a href="{{ route('products.create') }}"
            class="flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium rounded-xl shadow-sm shadow-blue-600/20 transition-colors">
            <x-pos-icon name="plus" class="w-4 h-4" /> Add Product
        </a>
        @endcan
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 mb-5">
        <div class="flex flex-wrap gap-3">
            <div class="relative flex-1 min-w-48">
                <x-pos-icon name="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                <input wire:model.live.debounce.350ms="search" type="text" placeholder="Search by name, barcode, SKU..."
                    class="w-full pl-9 pr-4 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400">
            </div>
            <select wire:model.live="categoryFilter"
                class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="statusFilter"
                class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                <option value="">All Status</option>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 text-xs uppercase tracking-wide">
                        <th class="text-left px-5 py-3 font-medium">
                            <button wire:click="sortBy('name')" class="flex items-center gap-1 hover:text-slate-700">
                                Product {{ $sortBy === 'name' ? ($sortDir === 'asc' ? '↑' : '↓') : '' }}
                            </button>
                        </th>
                        <th class="text-left px-5 py-3 font-medium">Category</th>
                        <th class="text-left px-5 py-3 font-medium">Barcode / SKU</th>
                        <th class="text-right px-5 py-3 font-medium">Cost</th>
                        <th class="text-right px-5 py-3 font-medium">
                            <button wire:click="sortBy('selling_price')" class="flex items-center gap-1 hover:text-slate-700 ml-auto">
                                Price {{ $sortBy === 'selling_price' ? ($sortDir === 'asc' ? '↑' : '↓') : '' }}
                            </button>
                        </th>
                        <th class="text-center px-5 py-3 font-medium">Tax %</th>
                        <th class="text-center px-5 py-3 font-medium">Status</th>
                        <th class="text-right px-5 py-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($products as $product)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-slate-100 flex items-center justify-center flex-shrink-0">
                                    @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" class="w-9 h-9 rounded-lg object-cover">
                                    @else
                                    <x-pos-icon name="cube" class="w-4 h-4 text-slate-400" />
                                    @endif
                                </div>
                                <div>
                                    <p class="font-medium text-slate-800">{{ $product->name }}</p>
                                    <p class="text-xs text-slate-400">{{ $product->unit?->abbreviation }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            @if($product->category)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium text-white"
                                    style="background-color: {{ $product->category->color ?? '#6B7280' }};">
                                {{ $product->category->name }}
                            </span>
                            @else
                            <span class="text-slate-400 text-xs">–</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-slate-500 font-mono text-xs">
                            <div>{{ $product->barcode ?: '–' }}</div>
                            <div class="text-slate-400">{{ $product->sku ?: '' }}</div>
                        </td>
                        <td class="px-5 py-3 text-right text-slate-600">${{ number_format($product->cost_price, 2) }}</td>
                        <td class="px-5 py-3 text-right font-semibold text-slate-800">${{ number_format($product->selling_price, 2) }}</td>
                        <td class="px-5 py-3 text-center text-slate-600">{{ $product->tax_rate }}%</td>
                        <td class="px-5 py-3 text-center">
                            <button wire:click="toggleStatus({{ $product->id }})"
                                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium transition-colors
                                       {{ $product->is_active ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}">
                                {{ $product->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-1.5">
                                @can('edit products')
                                <a href="{{ route('products.edit', $product) }}"
                                    class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors inline-flex">
                                    <x-pos-icon name="pencil" class="w-4 h-4" />
                                </a>
                                @endcan
                                @can('delete products')
                                <button wire:click="confirmDelete({{ $product->id }})"
                                    class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                    <x-pos-icon name="trash" class="w-4 h-4" />
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-16 text-center text-slate-400">
                            <x-pos-icon name="cube" class="w-10 h-10 mx-auto mb-3 text-slate-300" />
                            <p class="font-medium">No products found</p>
                            <p class="text-sm mt-1">Try adjusting your search filters</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($products->hasPages())
        <div class="px-5 py-3 border-t border-slate-100">
            {{ $products->links() }}
        </div>
        @endif
    </div>

    {{-- Product Modal --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto" wire:click.stop>
            <div class="flex items-center justify-between p-6 border-b border-slate-100">
                <h3 class="text-lg font-semibold text-slate-800">{{ $editingId ? 'Edit Product' : 'Add Product' }}</h3>
                <button wire:click="$set('showModal', false)" class="p-2 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100">
                    <x-pos-icon name="x-mark" class="w-5 h-5" />
                </button>
            </div>
            <form wire:submit="save" class="p-6 space-y-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Product Name <span class="text-red-500">*</span></label>
                        <input wire:model="name" type="text" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400" placeholder="e.g. Wireless Mouse">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Barcode</label>
                        <input wire:model="barcode" type="text" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 font-mono" placeholder="EAN / UPC">
                        @error('barcode') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">SKU</label>
                        <input wire:model="sku" type="text" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 font-mono" placeholder="Internal code">
                        @error('sku') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Category</label>
                        <select wire:model="category_id" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                            <option value="">Select Category</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Unit</label>
                        <select wire:model="unit_id" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                            <option value="">Select Unit</option>
                            @foreach($units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->abbreviation }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Supplier</label>
                        <select wire:model="supplier_id" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                            <option value="">Select Supplier</option>
                            @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}">{{ $sup->name }}{{ $sup->company ? ' – ' . $sup->company : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Cost Price <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">$</span>
                            <input wire:model="cost_price" type="number" step="0.01" min="0" class="w-full pl-7 pr-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400">
                        </div>
                        @error('cost_price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Selling Price <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">$</span>
                            <input wire:model="selling_price" type="number" step="0.01" min="0" class="w-full pl-7 pr-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400">
                        </div>
                        @error('selling_price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Tax Rate (%)</label>
                        <input wire:model="tax_rate" type="number" step="0.01" min="0" max="100" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Low Stock Alert (qty)</label>
                        <input wire:model="min_stock_alert" type="number" min="0" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Description</label>
                        <textarea wire:model="description" rows="3" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400 resize-none" placeholder="Optional description..."></textarea>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Product Image</label>
                        <input wire:model="image" type="file" accept="image/*" class="w-full text-sm text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        @error('image') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex items-center gap-2">
                        <input wire:model="track_inventory" type="checkbox" id="track_inventory" class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500">
                        <label for="track_inventory" class="text-sm text-slate-700">Track Inventory</label>
                    </div>
                    <div class="flex items-center gap-2">
                        <input wire:model="is_active" type="checkbox" id="is_active" class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500">
                        <label for="is_active" class="text-sm text-slate-700">Active</label>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2 border-t border-slate-100">
                    <button type="button" wire:click="$set('showModal', false)"
                        class="px-4 py-2 text-sm font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-5 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-500 rounded-xl shadow-sm shadow-blue-600/20 transition-colors">
                        {{ $editingId ? 'Save Changes' : 'Create Product' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Delete Confirmation --}}
    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <x-pos-icon name="exclamation-triangle" class="w-6 h-6 text-red-600" />
                </div>
                <div>
                    <h3 class="font-semibold text-slate-800">Delete Product</h3>
                    <p class="text-sm text-slate-500 mt-0.5">This action cannot be undone.</p>
                </div>
            </div>
            <div class="flex justify-end gap-3">
                <button wire:click="$set('showDeleteModal', false)"
                    class="px-4 py-2 text-sm font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl">Cancel</button>
                <button wire:click="delete"
                    class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-500 rounded-xl">Delete</button>
            </div>
        </div>
    </div>
    @endif
</div>
