<?php

namespace App\Livewire\Users;

use App\Models\Branch;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserList extends Component
{
    use WithPagination;

    public string $search    = '';
    public string $role      = '';
    public string $branch_id = '';

    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $editingId  = null;
    public ?int $deletingId = null;

    // Form
    public string  $name      = '';
    public string  $email     = '';
    public string  $phone     = '';
    public string  $password  = '';
    public string  $password_confirmation = '';
    public ?int    $form_branch_id = null;
    public string  $selectedRole  = 'cashier';
    public array   $selectedPermissions = [];
    public bool    $is_active     = true;

    protected function rules(): array
    {
        $passwordRule = $this->editingId ? 'nullable|min:8|confirmed' : 'required|min:8|confirmed';
        return [
            'name'            => 'required|string|max:100',
            'email'           => 'required|email|unique:users,email,' . ($this->editingId ?? 'NULL'),
            'phone'           => 'nullable|string|max:30',
            'password'        => $passwordRule,
            'selectedRole'    => 'required|exists:roles,name',
            'selectedPermissions' => 'array',
            'selectedPermissions.*' => 'string|exists:permissions,name',
            'form_branch_id'  => 'nullable|exists:branches,id',
            'is_active'       => 'boolean',
        ];
    }

    public function updatingSearch(): void { $this->resetPage(); }

    public function openCreate(): void
    {
        Gate::authorize('create users');
        $this->resetForm();
        $this->form_branch_id = Auth::user()?->branch_id;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        Gate::authorize('edit users');

        $u = User::findOrFail($id);
        abort_unless($u->branch_id === Auth::user()?->branch_id, 403);

        $this->editingId       = $id;
        $this->name            = $u->name;
        $this->email           = $u->email;
        $this->phone           = $u->phone ?? '';
        $this->form_branch_id  = $u->branch_id;
        $this->is_active       = (bool) $u->is_active;
        $this->selectedRole    = $u->roles->first()?->name ?? 'cashier';
        $this->selectedPermissions = $u->getDirectPermissions()->pluck('name')->values()->all();
        $this->password        = $this->password_confirmation = '';
        $this->showModal       = true;
    }

    public function save(): void
    {
        Gate::authorize($this->editingId ? 'edit users' : 'create users');
        $this->validate();

        $data = [
            'name'      => $this->name,
            'email'     => $this->email,
            'phone'     => $this->phone ?: null,
            'branch_id' => Auth::user()?->branch_id,
            'is_active' => $this->is_active,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->editingId) {
            $user = User::findOrFail($this->editingId);
            abort_unless($user->branch_id === Auth::user()?->branch_id, 403);
            $user->update($data);
        } else {
            $user = User::create($data);
        }

        $user->syncRoles([$this->selectedRole]);
        $user->syncPermissions($this->selectedPermissions);

        session()->flash('success', 'User saved.');
        $this->showModal = false;
        $this->resetForm();
    }

    public function toggleActive(int $id): void
    {
        Gate::authorize('edit users');

        $u = User::findOrFail($id);
        abort_unless($u->branch_id === Auth::user()?->branch_id, 403);

        if ($u->id === Auth::id()) {
            session()->flash('error', 'You cannot disable your own account while signed in.');
            return;
        }

        $u->update(['is_active' => !$u->is_active]);
    }

    public function confirmDelete(int $id): void
    {
        Gate::authorize('delete users');

        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        Gate::authorize('delete users');

        if ($this->deletingId) {
            if ($this->deletingId === Auth::id()) {
                session()->flash('error', 'You cannot delete your own account while signed in.');
            } else {
                $user = User::findOrFail($this->deletingId);
                abort_unless($user->branch_id === Auth::user()?->branch_id, 403);

                if ($user->sales()->exists() || $user->expenses()->exists() || PurchaseOrder::where('created_by', $user->id)->exists()) {
                    session()->flash('error', 'User cannot be deleted because sales, expenses, or purchase orders are linked to it.');
                } else {
                    $user->delete();
                    session()->flash('success', 'User deleted.');
                }
            }
        }
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    public function forceSignOut(int $id): void
    {
        Gate::authorize('edit users');
        $currentUser = Auth::user();
        abort_unless($currentUser instanceof User && $currentUser->hasRole('super_admin'), 403);

        $user = User::findOrFail($id);
        abort_unless($user->branch_id === Auth::user()?->branch_id, 403);

        if ($user->id === Auth::id()) {
            session()->flash('error', 'You cannot force sign out your current account from this action.');
            return;
        }

        DB::table(config('session.table', 'sessions'))
            ->where('user_id', $user->id)
            ->delete();

        $user->forceFill([
            'remember_token' => Str::random(60),
        ])->save();

        session()->flash('success', 'User has been signed out from all active sessions.');
    }

    private function resetForm(): void
    {
        $this->editingId    = null;
        $this->name         = $this->email = $this->phone = '';
        $this->password     = $this->password_confirmation = '';
        $this->selectedRole = 'cashier';
        $this->selectedPermissions = [];
        $this->form_branch_id = null;
        $this->is_active    = true;
        $this->resetValidation();
    }

    public function render()
    {
        Gate::authorize('view users');

        $users = User::with(['roles', 'branch'])
            ->where('branch_id', Auth::user()?->branch_id)
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%"))
            ->when($this->role, fn($q) => $q->whereHas('roles', fn($r) => $r->where('name', $this->role)))
            ->orderBy('name')
            ->paginate(20);

        $roles    = Role::orderBy('name')->get();
        $permissions = Permission::orderBy('name')->get();
        $branches = Branch::where('is_active', true)
            ->where('id', Auth::user()?->branch_id)
            ->get();

        return view('livewire.users.user-list', compact('users', 'roles', 'permissions', 'branches'))
            ->layout('layouts.app', ['title' => 'Users']);
    }
}
