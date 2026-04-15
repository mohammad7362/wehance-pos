<div>
    <div class="space-y-4">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <input wire:model.live.debounce.300ms="search" type="text"
                    placeholder="{{ __('Search categories...') }}"
                    class="input-field w-64" />
            </div>
            <button wire:click="openCreate" class="btn-primary flex items-center gap-2">
                <x-pos-icon name="plus" class="w-4 h-4" />
                {{ __('Add Category') }}
            </button>
        </div>

        {{-- Flash --}}
        @if(session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif

        {{-- Table --}}
        <div class="card overflow-hidden">
            <table class="w-full text-sm">
                <thead class="table-header">
                    <tr>
                        <th class="px-4 py-3 text-left">{{ __('Name') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('Parent') }}</th>
                        <th class="px-4 py-3 text-center">{{ __('Products') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($categories as $cat)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full inline-block" @style(['background-color: ' . ($cat->color ?? '#6366f1')])></span>
                                    <span class="font-medium text-gray-900">{{ $cat->name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-500">{{ $cat->parent?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="badge-blue">{{ $cat->products_count }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button wire:click="openEdit({{ $cat->id }})" class="btn-icon-edit">
                                        <x-pos-icon name="pencil" class="w-4 h-4" />
                                    </button>
                                    <button wire:click="confirmDelete({{ $cat->id }})" class="btn-icon-delete">
                                        <x-pos-icon name="trash" class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-12 text-center text-gray-400">{{ __('No categories found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-4 py-3 border-t">{{ $categories->links() }}</div>
        </div>
    </div>

    {{-- Create/Edit Modal --}}
    @if($showModal)
    <div class="modal-overlay" wire:click.self="$set('showModal',false)">
        <div class="modal-box max-w-md w-full">
            <h3 class="modal-title">{{ $editingId ? __('Edit Category') : __('New Category') }}</h3>
            <div class="space-y-4 mt-4">
                <div>
                    <label class="form-label">{{ __('Name') }} *</label>
                    <input wire:model="name" type="text" class="input-field w-full" placeholder="{{ __('Category name') }}" />
                    @error('name') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">{{ __('Parent Category') }}</label>
                    <select wire:model="parent_id" class="input-field w-full">
                        <option value="">{{ __('— None —') }}</option>
                        @foreach($allCategories as $c)
                            @if(!$editingId || $c->id !== $editingId)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">{{ __('Description') }}</label>
                    <textarea wire:model="description" class="input-field w-full" rows="2"></textarea>
                </div>
                <div>
                    <label class="form-label">{{ __('Color') }}</label>
                    <input wire:model="color" type="color" class="h-9 w-full rounded cursor-pointer" />
                </div>
            </div>
            <div class="modal-footer">
                <button wire:click="$set('showModal',false)" class="btn-secondary">{{ __('Cancel') }}</button>
                <button wire:click="save" class="btn-primary">{{ __('Save') }}</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Delete Confirm Modal --}}
    @if($showDeleteModal)
    <div class="modal-overlay">
        <div class="modal-box max-w-sm w-full text-center">
            <x-pos-icon name="exclamation-triangle" class="w-12 h-12 text-red-500 mx-auto mb-3" />
            <h3 class="text-lg font-semibold">{{ __('Delete Category?') }}</h3>
            <p class="text-gray-500 text-sm mt-1">{{ __('Child categories will be moved to the parent level.') }}</p>
            <div class="modal-footer justify-center">
                <button wire:click="$set('showDeleteModal',false)" class="btn-secondary">{{ __('Cancel') }}</button>
                <button wire:click="delete" class="btn-danger">{{ __('Delete') }}</button>
            </div>
        </div>
    </div>
    @endif
</div>
