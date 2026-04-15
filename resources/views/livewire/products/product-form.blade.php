<div>
    <div class="mb-4">
        <a href="{{ route('products.index') }}" class="btn-secondary inline-flex items-center gap-2">
            <x-pos-icon name="arrow-left" class="w-4 h-4" />
            Back to Products
        </a>
    </div>

    @if(session('success'))
        <div class="alert-success mb-4">{{ session('success') }}</div>
    @endif

    <div class="card p-6 max-w-5xl">
        <h2 class="text-lg font-bold mb-5">{{ $isEdit ? 'Edit Product' : 'Create Product' }}</h2>

        <form wire:submit="save" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="form-label">Product Name *</label>
                <input wire:model="name" type="text" class="input-field w-full" />
                @error('name') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="form-label">Barcode</label>
                <input wire:model="barcode" type="text" class="input-field w-full" />
                @error('barcode') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="form-label">SKU</label>
                <input wire:model="sku" type="text" class="input-field w-full" />
                @error('sku') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="form-label">Category</label>
                <select wire:model="category_id" class="input-field w-full">
                    <option value="">Select category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="form-label">Unit</label>
                <select wire:model="unit_id" class="input-field w-full">
                    <option value="">Select unit</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->abbreviation }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="form-label">Supplier</label>
                <select wire:model="supplier_id" class="input-field w-full">
                    <option value="">Select supplier</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="form-label">Tax Rate (%)</label>
                <input wire:model="tax_rate" type="number" min="0" max="100" step="0.01" class="input-field w-full" />
                @error('tax_rate') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="form-label">Cost Price *</label>
                <input wire:model="cost_price" type="number" min="0" step="0.01" class="input-field w-full" />
                @error('cost_price') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="form-label">Selling Price *</label>
                <input wire:model="selling_price" type="number" min="0" step="0.01" class="input-field w-full" />
                @error('selling_price') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="form-label">Low Stock Alert</label>
                <input wire:model="min_stock_alert" type="number" min="0" class="input-field w-full" />
                @error('min_stock_alert') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
                <label class="form-label">Description</label>
                <textarea wire:model="description" rows="3" class="input-field w-full"></textarea>
            </div>

            <div class="md:col-span-2">
                <label class="form-label">Product Image</label>
                <input wire:model="image" type="file" accept="image/*" class="input-field w-full" />
                @error('image') <p class="form-error">{{ $message }}</p> @enderror
                @if($currentImage && !$image)
                    <p class="text-xs text-gray-500 mt-2">Current image: {{ $currentImage }}</p>
                @endif
            </div>

            <div class="flex items-center gap-2">
                <input wire:model="track_inventory" id="track_inventory" type="checkbox" class="w-4 h-4" />
                <label for="track_inventory" class="text-sm text-gray-700">Track inventory</label>
            </div>

            <div class="flex items-center gap-2">
                <input wire:model="is_active" id="is_active" type="checkbox" class="w-4 h-4" />
                <label for="is_active" class="text-sm text-gray-700">Active</label>
            </div>

            <div class="md:col-span-2 flex justify-end gap-3 pt-3 border-t">
                <a href="{{ route('products.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">{{ $isEdit ? 'Save Changes' : 'Create Product' }}</button>
            </div>
        </form>
    </div>
</div>
