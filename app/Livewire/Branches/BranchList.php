<?php

namespace App\Livewire\Branches;

use App\Models\Branch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class BranchList extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $editingId  = null;
    public ?int $deletingId = null;

    // Form
    public string $name     = '';
    public string $code     = '';
    public string $address  = '';
    public string $city     = '';
    public string $phone    = '';
    public string $email    = '';
    public string $currency = 'USD';
    public string $tax_rate = '0';
    public string $receipt_footer = '';
    public bool   $is_active = true;

    protected function rules(): array
    {
        return [
            'name'      => 'required|string|max:100',
            'code'      => ['required', 'string', 'max:20', Rule::unique('branches', 'code')->ignore($this->editingId)],
            'address'   => 'nullable|string|max:500',
            'city'      => 'nullable|string|max:150',
            'phone'     => 'nullable|string|max:30',
            'email'     => 'nullable|email|max:150',
            'currency'  => 'required|string|max:10',
            'tax_rate'  => 'required|numeric|min:0|max:100',
            'receipt_footer' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ];
    }

    public function updatingSearch(): void { $this->resetPage(); }

    public function openCreate(): void
    {
        if ($this->currentBranchId()) {
            session()->flash('error', __('Each account can manage only its own branch.'));
            return;
        }

        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $b = $this->ownedBranch($id);
        $this->editingId = $id;
        $this->name      = $b->name;
        $this->code      = $b->code;
        $this->address   = $b->address ?? '';
        $this->city      = $b->city ?? '';
        $this->phone     = $b->phone ?? '';
        $this->email     = $b->email ?? '';
        $this->currency  = $b->currency ?? 'USD';
        $this->tax_rate  = (string) ($b->tax_rate ?? '0');
        $this->receipt_footer = $b->receipt_footer ?? '';
        $this->is_active = (bool) $b->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->code = strtoupper(trim($this->code));
        $this->currency = strtoupper(trim($this->currency));
        $this->validate();

        $data = [
            'name'      => $this->name,
            'code'      => $this->code,
            'address'   => $this->address ?: null,
            'city'      => $this->city ?: null,
            'phone'     => $this->phone ?: null,
            'email'     => $this->email ?: null,
            'currency'  => $this->currency,
            'currency_symbol' => $this->currencySymbol($this->currency),
            'tax_rate'  => $this->tax_rate,
            'receipt_footer' => $this->receipt_footer ?: null,
            'is_active' => $this->is_active,
        ];

        if ($this->editingId) {
            $this->ownedBranch($this->editingId)->update($data);
        } else {
            $ownerId = Auth::id();
            abort_unless($ownerId, 403);

            $branch = Branch::create($data + ['created_by' => $ownerId]);
            Auth::user()?->forceFill(['branch_id' => $branch->id])->save();
        }

        session()->flash('success', __('Branch saved.'));
        $this->showModal = false;
        $this->resetForm();
    }

    public function toggleActive(int $id): void
    {
        $b = $this->ownedBranch($id);
        $b->update(['is_active' => !$b->is_active]);
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId      = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            $branch = $this->ownedBranch($this->deletingId);

            if ($branch->sales()->exists() || $branch->purchaseOrders()->exists() || $branch->expenses()->exists()) {
                session()->flash('error', __('Branch cannot be deleted because sales, expenses, or purchase orders are linked to it.'));
            } else {
                $branch->delete();
                session()->flash('success', __('Branch deleted.'));
            }
        }
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = $this->code = $this->address = $this->city = $this->phone = $this->email = $this->receipt_footer = '';
        $this->currency = 'USD';
        $this->tax_rate = '0';
        $this->is_active = true;
        $this->resetValidation();
    }

    private function currencySymbol(string $currency): string
    {
        return match ($currency) {
            'USD' => '$',
            'EUR' => 'EUR',
            'GBP' => 'GBP',
            'AED' => 'AED',
            'SAR' => 'SAR',
            default => $currency,
        };
    }

    public function render()
    {
        $branchId = $this->currentBranchId();

        $branches = Branch::withCount('users')
            ->when(
                $branchId,
                fn($q) => $q->whereKey($branchId),
                fn($q) => $q->whereRaw('1 = 0')
            )
            ->when($this->search, function ($q) {
                $search = "%{$this->search}%";
                $q->where(function ($sq) use ($search) {
                    $sq->where('name', 'like', $search)
                        ->orWhere('code', 'like', $search);
                });
            })
            ->orderBy('name')
            ->paginate(20);

        return view('livewire.branches.branch-list', compact('branches'))
            ->layout('layouts.app', ['title' => 'Branches']);
    }

    private function ownedBranch(int $id): Branch
    {
        $branchId = $this->currentBranchId();

        abort_unless($branchId, 403);
        abort_unless($branchId === $id, 403);

        return Branch::findOrFail($branchId);
    }

    private function currentBranchId(): ?int
    {
        $user = Auth::user();

        return $user instanceof \App\Models\User ? $user->branch_id : null;
    }
}
