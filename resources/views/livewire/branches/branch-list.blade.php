<div>
    <div class="flex items-center justify-between mb-4">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="{{ __('Search branches...') }}" class="input-field w-64" />
        <button wire:click="openCreate" class="btn-primary flex items-center gap-2">
            <x-pos-icon name="plus" class="w-4 h-4" /> {{ __('Add Branch') }}
        </button>
    </div>

    @if(session('success'))
        <div class="alert-success mb-3">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="mb-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
    @endif

    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="table-header">
                <tr>
                    <th class="px-4 py-3 text-left">{{ __('Name') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Code') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('City') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Currency') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Phone') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Tax') }}</th>
                    <th class="px-4 py-3 text-center">{{ __('Users') }}</th>
                    <th class="px-4 py-3 text-center">{{ __('Status') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($branches as $b)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium">{{ $b->name }}</td>
                        <td class="px-4 py-3"><span class="font-mono badge-blue">{{ $b->code }}</span></td>
                        <td class="px-4 py-3 text-gray-500">{{ $b->city ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $b->currency_symbol ?? $b->currency }} {{ $b->currency }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $b->phone ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ number_format((float) $b->tax_rate, 2) }}%</td>
                        <td class="px-4 py-3 text-center"><span class="badge-blue">{{ $b->users_count }}</span></td>
                        <td class="px-4 py-3 text-center">
                            <button wire:click="toggleActive({{ $b->id }})">
                                <span class="badge-{{ $b->is_active ? 'green' : 'red' }} cursor-pointer">
                                    {{ $b->is_active ? __('Active') : __('Inactive') }}
                                </span>
                            </button>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button wire:click="openEdit({{ $b->id }})" class="btn-icon-edit">
                                    <x-pos-icon name="pencil" class="w-4 h-4" />
                                </button>
                                <button wire:click="confirmDelete({{ $b->id }})" class="btn-icon-delete">
                                    <x-pos-icon name="trash" class="w-4 h-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="px-4 py-12 text-center text-gray-400">{{ __('No branches found.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t">{{ $branches->links() }}</div>
    </div>

    @if($showModal)
    <div class="modal-overlay" wire:click.self="$set('showModal',false)">
        <div class="modal-box max-w-lg w-full">
            <h3 class="modal-title">{{ $editingId ? __('Edit Branch') : __('New Branch') }}</h3>
            <div class="grid grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="form-label">{{ __('Branch Name') }} *</label>
                    <input wire:model="name" type="text" class="input-field w-full" />
                    @error('name') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">{{ __('Code') }} *</label>
                    <input wire:model="code" type="text" class="input-field w-full uppercase" placeholder="{{ __('e.g. MAIN') }}" />
                    @error('code') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">{{ __('Phone') }}</label>
                    <input wire:model="phone" type="text" class="input-field w-full" />
                </div>
                <div>
                    <label class="form-label">{{ __('Email') }}</label>
                    <input wire:model="email" type="email" class="input-field w-full" />
                </div>
                <div>
                    <label class="form-label">{{ __('City') }}</label>
                    <input wire:model="city" type="text" class="input-field w-full" />
                    @error('city') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">{{ __('Currency') }} *</label>
                    <select wire:model="currency" class="input-field w-full">
                        <option value="USD">USD - US Dollar</option>
                        <option value="EUR">EUR - Euro</option>
                        <option value="GBP">GBP - British Pound</option>
                        <option value="AED">AED - UAE Dirham</option>
                        <option value="SAR">SAR - Saudi Riyal</option>
                    </select>
                    @error('currency') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">{{ __('Default Tax Rate (%)') }} *</label>
                    <input wire:model="tax_rate" type="number" min="0" max="100" step="0.1" class="input-field w-full" />
                    @error('tax_rate') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div class="col-span-2">
                    <label class="form-label">{{ __('Address') }}</label>
                    <textarea wire:model="address" class="input-field w-full" rows="2"></textarea>
                    @error('address') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div class="col-span-2">
                    <label class="form-label">{{ __('Receipt Footer') }}</label>
                    <textarea wire:model="receipt_footer" class="input-field w-full" rows="2" placeholder="{{ __('Thank you for your business!') }}"></textarea>
                    @error('receipt_footer') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div class="flex items-center gap-2">
                    <input wire:model="is_active" type="checkbox" id="br_active" class="w-4 h-4" />
                    <label for="br_active" class="text-sm">{{ __('Active') }}</label>
                </div>
            </div>
            <div class="modal-footer">
                <button wire:click="$set('showModal',false)" class="btn-secondary">{{ __('Cancel') }}</button>
                <button wire:click="save" class="btn-primary">{{ __('Save') }}</button>
            </div>
        </div>
    </div>
    @endif

    @if($showDeleteModal)
    <div class="modal-overlay">
        <div class="modal-box max-w-sm w-full text-center">
            <x-pos-icon name="exclamation-triangle" class="w-12 h-12 text-red-500 mx-auto mb-3" />
            <h3 class="text-lg font-semibold">{{ __('Delete Branch?') }}</h3>
            <p class="text-gray-500 text-sm mt-1">{{ __('This cannot be undone.') }}</p>
            <div class="modal-footer justify-center">
                <button wire:click="$set('showDeleteModal',false)" class="btn-secondary">{{ __('Cancel') }}</button>
                <button wire:click="delete" class="btn-danger">{{ __('Delete') }}</button>
            </div>
        </div>
    </div>
    @endif
</div>
