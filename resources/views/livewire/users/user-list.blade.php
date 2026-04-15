<div>
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search users..." class="input-field w-56" />
            <select wire:model.live="role" class="input-field">
                <option value="">All Roles</option>
                @foreach($roles as $r)
                    <option value="{{ $r->name }}">{{ ucwords(str_replace('_',' ',$r->name)) }}</option>
                @endforeach
            </select>
            <select wire:model.live="branch_id" class="input-field">
                <option value="">All Branches</option>
                @foreach($branches as $b)
                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                @endforeach
            </select>
        </div>
        <button wire:click="openCreate" class="btn-primary flex items-center gap-2">
            <x-pos-icon name="plus" class="w-4 h-4" /> Add User
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
                    <th class="px-4 py-3 text-left">Name</th>
                    <th class="px-4 py-3 text-left">Email</th>
                    <th class="px-4 py-3 text-left">Role</th>
                    <th class="px-4 py-3 text-left">Branch</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($users as $u)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium">{{ $u->name }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $u->email }}</td>
                        <td class="px-4 py-3">
                            @foreach($u->roles as $role)
                                <span class="badge-blue">{{ ucwords(str_replace('_',' ',$role->name)) }}</span>
                            @endforeach
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $u->branch?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            <button wire:click="toggleActive({{ $u->id }})">
                                <span class="badge-{{ $u->is_active ? 'green' : 'red' }} cursor-pointer">
                                    {{ $u->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </button>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button wire:click="openEdit({{ $u->id }})" class="btn-icon-edit">
                                    <x-pos-icon name="pencil" class="w-4 h-4" />
                                </button>
                                @if($u->id !== auth()->id())
                                    <button wire:click="confirmDelete({{ $u->id }})" class="btn-icon-delete">
                                        <x-pos-icon name="trash" class="w-4 h-4" />
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t">{{ $users->links() }}</div>
    </div>

    @if($showModal)
    <div class="modal-overlay" wire:click.self="$set('showModal',false)">
        <div class="modal-box max-w-lg w-full">
            <h3 class="modal-title">{{ $editingId ? 'Edit User' : 'New User' }}</h3>
            <div class="grid grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="form-label">Full Name *</label>
                    <input wire:model="name" type="text" class="input-field w-full" />
                    @error('name') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Email *</label>
                    <input wire:model="email" type="email" class="input-field w-full" />
                    @error('email') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Phone</label>
                    <input wire:model="phone" type="text" class="input-field w-full" />
                </div>
                <div>
                    <label class="form-label">Role *</label>
                    <select wire:model="selectedRole" class="input-field w-full">
                        @foreach($roles as $r)
                            <option value="{{ $r->name }}">{{ ucwords(str_replace('_',' ',$r->name)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Branch</label>
                    <select wire:model="form_branch_id" class="input-field w-full">
                        <option value="">— None —</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-2 mt-4">
                    <input wire:model="is_active" type="checkbox" id="usr_active" class="w-4 h-4" />
                    <label for="usr_active" class="text-sm">Active</label>
                </div>
                <div>
                    <label class="form-label">Password {{ $editingId ? '(leave blank to keep)' : '*' }}</label>
                    <input wire:model="password" type="password" class="input-field w-full" autocomplete="new-password" />
                    @error('password') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Confirm Password</label>
                    <input wire:model="password_confirmation" type="password" class="input-field w-full" autocomplete="new-password" />
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
            <h3 class="text-lg font-semibold">Delete User?</h3>
            <div class="modal-footer justify-center">
                <button wire:click="$set('showDeleteModal',false)" class="btn-secondary">Cancel</button>
                <button wire:click="delete" class="btn-danger">Delete</button>
            </div>
        </div>
    </div>
    @endif
</div>
