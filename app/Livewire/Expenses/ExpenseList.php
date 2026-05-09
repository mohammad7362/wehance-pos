<?php

namespace App\Livewire\Expenses;

use App\Models\Branch;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ExpenseList extends Component
{
    use WithPagination;

    public string $search      = '';
    public string $branch_id   = '';
    public string $category_id = '';
    public string $dateFrom    = '';
    public string $dateTo      = '';

    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $editingId  = null;
    public ?int $deletingId = null;

    // Form
    public ?int    $expense_category_id = null;
    public ?int    $form_branch_id      = null;
    public string  $description         = '';
    public float   $amount              = 0;
    public string  $date                = '';
    public string  $reference           = '';

    protected array $rules = [
        'expense_category_id' => 'required|exists:expense_categories,id',
        'form_branch_id'      => 'required|exists:branches,id',
        'description'         => 'required|string|max:300',
        'amount'              => 'required|numeric|min:0.01',
        'date'                => 'required|date',
        'reference'           => 'nullable|string|max:100',
    ];

    public function mount(): void
    {
        $this->date           = now()->toDateString();
        $this->form_branch_id = Auth::user()->branch_id ?? Branch::first()?->id;
    }

    public function updatingSearch(): void    { $this->resetPage(); }
    public function updatingCategoryId(): void { $this->resetPage(); }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $e = Expense::query()
            ->whereKey($id)
            ->where('branch_id', Auth::user()?->branch_id)
            ->firstOrFail();

        $this->editingId            = $id;
        $this->expense_category_id  = $e->expense_category_id;
        $this->form_branch_id       = $e->branch_id;
        $this->description          = $e->description;
        $this->amount               = (float) $e->amount;
        $this->date                 = now()->parse((string) $e->date)->toDateString();
        $this->reference            = $e->reference ?? '';
        $this->showModal            = true;
    }

    public function save(): void
    {
        $this->validate();

        $existingExpense = $this->editingId ? Expense::findOrFail($this->editingId) : null;

        $data = [
            'expense_category_id' => $this->expense_category_id,
            'branch_id'           => $this->form_branch_id,
            'description'         => $this->description,
            'amount'              => $this->amount,
            'date'                => $this->date,
            'reference'           => $this->reference ?: null,
            'user_id'             => $existingExpense?->user_id ?? Auth::id(),
        ];

        if ($this->editingId) {
            abort_unless($existingExpense && $existingExpense->branch_id === Auth::user()?->branch_id, 403);
            $existingExpense->update($data);
        } else {
            Expense::create($data);
        }

        session()->flash('success', 'Expense saved.');
        $this->showModal = false;
        $this->resetForm();
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId      = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            Expense::query()
                ->whereKey($this->deletingId)
                ->where('branch_id', Auth::user()?->branch_id)
                ->firstOrFail()
                ->delete();

            session()->flash('success', 'Expense deleted.');
        }
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    private function resetForm(): void
    {
        $this->editingId    = null;
        $this->description  = $this->reference = '';
        $this->amount       = 0;
        $this->date = now()->toDateString();
        $this->expense_category_id = null;
        $this->form_branch_id      = Auth::user()->branch_id ?? Branch::first()?->id;
        $this->resetValidation();
    }

    public function render()
    {
        $branchId = Auth::user()?->branch_id;

        $expenses = Expense::with(['category', 'branch', 'user'])
            ->where('branch_id', $branchId)
            ->when($this->search, fn($q) => $q->where('description', 'like', "%{$this->search}%"))
            ->when($this->category_id, fn($q) => $q->where('expense_category_id', $this->category_id))
            ->when($this->dateFrom, fn($q) => $q->whereDate('date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('date', '<=', $this->dateTo))
            ->latest('date')
            ->paginate(25);

        $branches   = Branch::where('is_active', true)
            ->where('id', $branchId)
            ->get();
        $categories = ExpenseCategory::orderBy('name')->get();

        return view('livewire.expenses.expense-list', compact('expenses', 'branches', 'categories'))
            ->layout('layouts.app', ['title' => 'Expenses']);
    }
}
