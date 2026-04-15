<?php

namespace App\Livewire\Reports;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Inventory;
use App\Support\Exports\ExcelReportExporter;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class InventoryReport extends Component
{
    use WithPagination;

    public string $search    = '';
    public string $branch_id = '';
    public string $category  = '';
    public string $stock     = '';

    public function exportExcel(): BinaryFileResponse
    {
        $inventory = $this->inventoryQuery()->get();
        $stats = $this->stats();

        return app(ExcelReportExporter::class)->download(
            'inventory-report-' . now()->format('Ymd-His') . '.xlsx',
            [
                [
                    'title' => 'Summary',
                    'headings' => ['Metric', 'Value'],
                    'rows' => [
                        ['Search', $this->search],
                        ['Branch', optional(Branch::find($this->branch_id))->name ?? 'All Branches'],
                        ['Category', optional(Category::find($this->category))->name ?? 'All Categories'],
                        ['Stock Filter', $this->stock ?: 'All Stock'],
                        ['Total SKUs', (int) $stats['total_items']],
                        ['Low Stock', (int) $stats['low_stock']],
                        ['Out of Stock', (int) $stats['out_of_stock']],
                        ['Total Value', (float) $stats['total_value']],
                    ],
                ],
                [
                    'title' => 'Inventory',
                    'headings' => ['Product', 'SKU', 'Category', 'Branch', 'Quantity', 'Reorder Level', 'Cost Price', 'Cost Value', 'Status'],
                    'rows' => $inventory->map(function ($item) {
                        $reorderLevel = $item->product?->min_stock_alert ?? 0;
                        $status = $item->quantity <= 0 ? 'Out' : ($item->quantity <= $reorderLevel ? 'Low' : 'OK');

                        return [
                            $item->product?->name,
                            $item->product?->sku,
                            $item->product?->category?->name,
                            $item->branch?->name,
                            (float) $item->quantity,
                            (float) $reorderLevel,
                            (float) ($item->product?->cost_price ?? 0),
                            (float) ($item->quantity * ($item->product?->cost_price ?? 0)),
                            $status,
                        ];
                    })->all(),
                ],
            ]
        );
    }

    public function render()
    {
        $inventory = $this->inventoryQuery()
            ->paginate(30);

        $branches   = Branch::where('is_active', true)->get();
        $categories = Category::orderBy('name')->get();

        $stats = $this->stats();

        return view('livewire.reports.inventory-report', compact('inventory', 'branches', 'categories', 'stats'))
            ->layout('layouts.app', ['title' => 'Inventory Report']);
    }

    private function inventoryQuery()
    {
        return Inventory::with(['product.category', 'branch'])
            ->when($this->search, fn ($query) => $query->whereHas('product', fn ($productQuery) => $productQuery->where('name', 'like', "%{$this->search}%")
                ->orWhere('sku', 'like', "%{$this->search}%")))
            ->when($this->branch_id, fn ($query) => $query->where('branch_id', $this->branch_id))
            ->when($this->category, fn ($query) => $query->whereHas('product', fn ($productQuery) => $productQuery->where('category_id', $this->category)))
            ->when($this->stock === 'out', fn ($query) => $query->where('quantity', '<=', 0))
            ->when($this->stock === 'low', fn ($query) => $query->where('quantity', '>', 0)->whereRaw('quantity <= (select min_stock_alert from products where products.id = inventory.product_id)'))
            ->when($this->stock === 'ok', fn ($query) => $query->whereRaw('quantity > (select min_stock_alert from products where products.id = inventory.product_id)'));
    }

    private function stats(): array
    {
        return [
            'total_items' => Inventory::count(),
            'low_stock' => Inventory::where('quantity', '>', 0)
                ->whereRaw('quantity <= (select min_stock_alert from products where products.id = inventory.product_id)')
                ->count(),
            'out_of_stock' => Inventory::where('quantity', '<=', 0)->count(),
            'total_value' => Inventory::join('products', 'inventory.product_id', '=', 'products.id')
                ->selectRaw('SUM(inventory.quantity * products.cost_price) as val')
                ->value('val') ?? 0,
        ];
    }
}
