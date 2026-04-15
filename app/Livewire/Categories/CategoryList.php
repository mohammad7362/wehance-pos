<?php

namespace App\Livewire\Categories;

use App\Models\Category;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class CategoryList extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $editingId = null;
    public ?int $deletingId = null;

    // Form fields
    public string $name = '';
    public string $description = '';
    public ?int $parent_id = null;
    public string $color = '#6366f1';

    protected array $rules = [
        'name'        => 'required|string|max:100',
        'description' => 'nullable|string|max:500',
        'parent_id'   => 'nullable|exists:categories,id',
        'color'       => 'required|string|max:20',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $category = Category::findOrFail($id);
        $this->editingId  = $id;
        $this->name        = $category->name;
        $this->description = $category->description ?? '';
        $this->parent_id   = $category->parent_id;
        $this->color       = $category->color ?? '#6366f1';
        $this->showModal   = true;
    }

    public function save(): void
    {
        $this->validate();

        $slug = Str::slug($this->name);

        $data = [
            'name'        => $this->name,
            'description' => $this->description ?: null,
            'parent_id'   => $this->parent_id,
            'color'       => $this->color,
        ];

        if ($this->editingId) {
            $category = Category::findOrFail($this->editingId);

            if ($category->name !== $this->name) {
                if (Category::where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
                    $slug .= '-' . $category->id;
                }
                $data['slug'] = $slug;
            }

            $category->update($data);
            session()->flash('success', __('Category updated successfully.'));
        } else {
            if (Category::where('slug', $slug)->exists()) {
                $slug .= '-' . time();
            }
            $data['slug'] = $slug;
            Category::create($data);
            session()->flash('success', __('Category created successfully.'));
        }

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
            $category = Category::findOrFail($this->deletingId);
            // Move children to parent
            Category::where('parent_id', $this->deletingId)->update(['parent_id' => $category->parent_id]);
            $category->delete();
            session()->flash('success', __('Category deleted successfully.'));
        }
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    private function resetForm(): void
    {
        $this->editingId   = null;
        $this->name        = '';
        $this->description = '';
        $this->parent_id   = null;
        $this->color       = '#6366f1';
        $this->resetValidation();
    }

    public function render()
    {
        $categories = Category::with('parent')
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->withCount('products')
            ->orderBy('name')
            ->paginate(20);

        $allCategories = Category::orderBy('name')->get();

        return view('livewire.categories.category-list', compact('categories', 'allCategories'))
            ->layout('layouts.app', ['title' => 'Categories']);
    }
}
