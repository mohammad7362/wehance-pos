<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use Livewire\Component;
use Livewire\WithPagination;

class CustomerList extends Component
{
    use WithPagination;

    public string $search = '';
    public bool   $showModal = false;
    public bool   $showDeleteModal = false;
    public ?int   $editingId  = null;
    public ?int   $deletingId = null;

    // Form
    public string $name      = '';
    public string $email     = '';
    public string $phone     = '';
    public string $address   = '';
    public string $date_of_birth  = '';
    public bool   $is_active = true;

    protected function rules(): array
    {
        return [
            'name'          => 'required|string|max:150',
            'email'         => 'nullable|email|max:150|unique:customers,email,' . ($this->editingId ?? 'NULL'),
            'phone'         => 'nullable|string|max:30',
            'address'       => 'nullable|string|max:500',
            'date_of_birth' => 'nullable|date',
            'is_active'     => 'boolean',
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
        $c = Customer::findOrFail($id);
        $this->editingId = $id;
        $this->name      = $c->name;
        $this->email     = $c->email ?? '';
        $this->phone     = $c->phone ?? '';
        $this->address   = $c->address ?? '';
        $this->date_of_birth  = $c->date_of_birth ? now()->parse((string) $c->date_of_birth)->toDateString() : '';
        $this->is_active = (bool) $c->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name'          => $this->name,
            'email'         => $this->email ?: null,
            'phone'         => $this->phone ?: null,
            'address'       => $this->address ?: null,
            'date_of_birth' => $this->date_of_birth ?: null,
            'is_active'     => $this->is_active,
        ];

        if ($this->editingId) {
            Customer::findOrFail($this->editingId)->update($data);
        } else {
            Customer::create($data);
        }

        session()->flash('success', __('Customer saved.'));
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
            Customer::findOrFail($this->deletingId)->delete();
            session()->flash('success', __('Customer deleted.'));
        }
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = $this->email = $this->phone = $this->address = $this->date_of_birth = '';
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        $customers = Customer::when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%")
                ->orWhere('phone', 'like', "%{$this->search}%"))
            ->withCount('sales')
            ->orderBy('name')
            ->paginate(25);

        return view('livewire.customers.customer-list', compact('customers'))
            ->layout('layouts.app', ['title' => 'Customers']);
    }
}
