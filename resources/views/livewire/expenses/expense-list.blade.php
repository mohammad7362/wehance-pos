<div>
    <div class="flex flex-wrap items-center gap-3 mb-4">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search description..." class="input-field w-56" />
        <select wire:model.live="branch_id" class="input-field">
            <option value="">All Branches</option>
            @foreach($branches as $b)
                <option value="{{ $b->id }}">{{ $b->name }}</option>
            @endforeach
        </select>
        <select wire:model.live="category_id" class="input-field">
            <option value="">All Categories</option>
            @foreach($categories as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
            @endforeach
        </select>
        <input wire:model.live="dateFrom" type="date" class="input-field" />
        <input wire:model.live="dateTo" type="date" class="input-field" />
        <button wire:click="openCreate" class="btn-primary flex items-center gap-2 ml-auto">
            <x-pos-icon name="plus" class="w-4 h-4" /> Add Expense
        </button>
    </div>

    @if(session('success'))
        <div class="alert-success mb-3">{{ session('success') }}</div>
    @endif

    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="table-header">
                <tr>
                    <th class="px-4 py-3 text-left">Date</th>
                    <th class="px-4 py-3 text-left">Category</th>
                    <th class="px-4 py-3 text-left">Description</th>
                    <th class="px-4 py-3 text-left">Branch</th>
                    <th class="px-4 py-3 text-center">Reference</th>
                    <th class="px-4 py-3 text-right">Amount</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($expenses as $e)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-500">{{ $e->date->format('M d, Y') }}</td>
                        <td class="px-4 py-3">{{ $e->category?->name ?? '—' }}</td>
                        <td class="px-4 py-3 font-medium">{{ $e->description }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $e->branch?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="badge-blue">{{ $e->reference ?? '—' }}</span>
                        </td>
                        <td class="px-4 py-3 text-right font-semibold text-red-600">${{ number_format($e->amount, 2) }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button wire:click="openEdit({{ $e->id }})" class="btn-icon-edit">
                                    <x-pos-icon name="pencil" class="w-4 h-4" />
                                </button>
                                <button wire:click="confirmDelete({{ $e->id }})" class="btn-icon-delete">
                                    <x-pos-icon name="trash" class="w-4 h-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400">No expenses found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t">{{ $expenses->links() }}</div>
    </div>

    @if($showModal)
    <div class="modal-overlay" wire:click.self="$set('showModal',false)">
        <div class="modal-box max-w-lg w-full">
            <h3 class="modal-title">{{ $editingId ? 'Edit Expense' : 'New Expense' }}</h3>
            <div class="grid grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="form-label">Category *</label>
                    <select wire:model="expense_category_id" class="input-field w-full">
                        <option value="">Select category...</option>
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                    @error('expense_category_id') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Branch *</label>
                    <select wire:model="form_branch_id" class="input-field w-full">
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-2">
                    <label class="form-label">Description *</label>
                    <input wire:model="description" type="text" class="input-field w-full" />
                    @error('description') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Amount *</label>
                    <input wire:model="amount" type="number" min="0.01" step="0.01" class="input-field w-full" />
                    @error('amount') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Date *</label>
                    <input wire:model="date" type="date" class="input-field w-full" />
                </div>
                <div>
                    <label class="form-label">Reference #</label>
                    <input wire:model="reference" type="text" class="input-field w-full" />
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
            <h3 class="text-lg font-semibold">Delete Expense?</h3>
            <div class="modal-footer justify-center">
                <button wire:click="$set('showDeleteModal',false)" class="btn-secondary">Cancel</button>
                <button wire:click="delete" class="btn-danger">Delete</button>
            </div>
        </div>
    </div>
    @endif
</div>
