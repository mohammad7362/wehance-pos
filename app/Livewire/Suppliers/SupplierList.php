<?php

namespace App\Livewire\Suppliers;

use App\Models\Supplier;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class SupplierList extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $editingId = null;
    public ?int $deletingId = null;

    // Form
    public string $name    = '';
    public string $email   = '';
    public string $phone   = '';
    public string $address = '';
    public string $company = '';
    public bool   $is_active = true;

    protected function rules(): array
    {
        return [
            'name'      => 'required|string|max:150',
            'company'   => 'nullable|string|max:150',
            'email'     => [
                'nullable',
                'email',
                'max:150',
                Rule::unique('suppliers', 'email')->ignore($this->editingId),
            ],
            'phone'     => 'nullable|string|max:30',
            'address'   => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ];
    }

    public function updatingSearch(): void { $this->resetPage(); }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $s = Supplier::findOrFail($id);
        $this->editingId      = $id;
        $this->name           = $s->name;
        $this->company        = $s->company ?? '';
        $this->email          = $s->email ?? '';
        $this->phone          = $s->phone ?? '';
        $this->address        = $s->address ?? '';
        $this->is_active      = (bool) $s->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name'      => $this->name,
            'company'   => $this->company ?: null,
            'email'     => $this->email ?: null,
            'phone'     => $this->phone ?: null,
            'address'   => $this->address ?: null,
            'is_active' => $this->is_active,
        ];

        if ($this->editingId) {
            Supplier::findOrFail($this->editingId)->update($data);
            session()->flash('success', __('Supplier updated.'));
        } else {
            Supplier::create($data);
            session()->flash('success', __('Supplier created.'));
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            $supplier = Supplier::findOrFail($this->deletingId);

            if ($supplier->purchaseOrders()->exists()) {
                session()->flash('error', __('Supplier cannot be deleted because purchase orders are linked to it.'));
            } else {
                $supplier->delete();
                session()->flash('success', __('Supplier deleted.'));
            }
        }
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = $this->company = $this->email = $this->phone = $this->address = '';
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        $suppliers = Supplier::when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%")
                ->orWhere('phone', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(20);

        return view('livewire.suppliers.supplier-list', compact('suppliers'))
            ->layout('layouts.app', ['title' => 'Suppliers']);
    }
}
