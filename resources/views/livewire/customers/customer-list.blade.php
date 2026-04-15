<div>
    <div class="flex items-center justify-between mb-4">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="{{ __('Search customers...') }}" class="input-field w-64" />
        <button wire:click="openCreate" class="btn-primary flex items-center gap-2">
            <x-pos-icon name="plus" class="w-4 h-4" /> {{ __('Add Customer') }}
        </button>
    </div>

    @if(session('success'))
        <div class="alert-success mb-3">{{ session('success') }}</div>
    @endif

    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="table-header">
                <tr>
                    <th class="px-4 py-3 text-left">{{ __('Name') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Email') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Phone') }}</th>
                    <th class="px-4 py-3 text-center">{{ __('Sales') }}</th>
                    <th class="px-4 py-3 text-center">{{ __('Points') }}</th>
                    <th class="px-4 py-3 text-center">{{ __('Status') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($customers as $c)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium">{{ $c->name }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $c->email ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $c->phone ?? '—' }}</td>
                        <td class="px-4 py-3 text-center"><span class="badge-blue">{{ $c->sales_count }}</span></td>
                        <td class="px-4 py-3 text-center font-semibold text-amber-600">{{ number_format($c->loyalty_points ?? 0) }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="badge-{{ $c->is_active ? 'green' : 'red' }}">{{ $c->is_active ? __('Active') : __('Inactive') }}</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button wire:click="openEdit({{ $c->id }})" class="btn-icon-edit">
                                    <x-pos-icon name="pencil" class="w-4 h-4" />
                                </button>
                                <button wire:click="confirmDelete({{ $c->id }})" class="btn-icon-delete">
                                    <x-pos-icon name="trash" class="w-4 h-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400">{{ __('No customers found.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t">{{ $customers->links() }}</div>
    </div>

    @if($showModal)
    <div class="modal-overlay" wire:click.self="$set('showModal',false)">
        <div class="modal-box max-w-lg w-full">
            <h3 class="modal-title">{{ $editingId ? __('Edit Customer') : __('New Customer') }}</h3>
            <div class="grid grid-cols-2 gap-4 mt-4">
                <div class="col-span-2">
                    <label class="form-label">{{ __('Full Name') }} *</label>
                    <input wire:model="name" type="text" class="input-field w-full" />
                    @error('name') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">{{ __('Email') }}</label>
                    <input wire:model="email" type="email" class="input-field w-full" />
                    @error('email') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">{{ __('Phone') }}</label>
                    <input wire:model="phone" type="text" class="input-field w-full" />
                </div>
                <div>
                    <label class="form-label">{{ __('Birthday') }}</label>
                    <input wire:model="date_of_birth" type="date" class="input-field w-full" />
                </div>
                <div class="flex items-center gap-2 mt-4">
                    <input wire:model="is_active" type="checkbox" id="cust_active" class="w-4 h-4" />
                    <label for="cust_active" class="text-sm">{{ __('Active') }}</label>
                </div>
                <div class="col-span-2">
                    <label class="form-label">{{ __('Address') }}</label>
                    <textarea wire:model="address" class="input-field w-full" rows="2"></textarea>
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
            <h3 class="text-lg font-semibold">{{ __('Delete Customer?') }}</h3>
            <div class="modal-footer justify-center">
                <button wire:click="$set('showDeleteModal',false)" class="btn-secondary">{{ __('Cancel') }}</button>
                <button wire:click="delete" class="btn-danger">{{ __('Delete') }}</button>
            </div>
        </div>
    </div>
    @endif
</div>
