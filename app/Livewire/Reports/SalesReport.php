<?php

namespace App\Livewire\Reports;

use App\Models\Branch;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Support\Exports\ExcelReportExporter;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SalesReport extends Component
{
    use WithPagination;

    public string $dateFrom  = '';
    public string $dateTo    = '';
    public string $branch_id = '';
    public string $groupBy   = 'day'; // day | week | month | product | category

    public function mount(): void
    {
        $this->dateFrom = now()->startOfMonth()->toDateString();
        $this->dateTo   = now()->toDateString();
    }

    public function exportExcel(): BinaryFileResponse
    {
        $summary = $this->summaryQuery();
        $sales = $this->salesQuery()->get();
        $topProducts = $this->topProductsQuery()->get();

        return app(ExcelReportExporter::class)->download(
            'sales-report-' . now()->format('Ymd-His') . '.xlsx',
            [
                [
                    'title' => 'Summary',
                    'headings' => ['Metric', 'Value'],
                    'rows' => [
                        ['Date From', $this->dateFrom ?: ''],
                        ['Date To', $this->dateTo ?: ''],
                        ['Branch', optional(Branch::find($this->branch_id))->name ?? 'All Branches'],
                        ['Total Sales', (int) ($summary?->total_sales ?? 0)],
                        ['Total Revenue', (float) ($summary?->total_revenue ?? 0)],
                        ['Average Sale', (float) ($summary?->avg_sale ?? 0)],
                    ],
                ],
                [
                    'title' => 'Sales',
                    'headings' => ['Invoice', 'Date', 'Branch', 'Customer', 'Subtotal', 'Tax', 'Discount', 'Total'],
                    'rows' => $sales->map(fn ($sale) => [
                        $sale->reference_no,
                        optional($sale->created_at)?->format('Y-m-d H:i:s'),
                        $sale->branch?->name,
                        $sale->customer?->name ?? 'Walk-in',
                        (float) $sale->subtotal,
                        (float) $sale->tax_amount,
                        (float) $sale->discount_amount,
                        (float) $sale->total,
                    ])->all(),
                ],
                [
                    'title' => 'Top Products',
                    'headings' => ['Product', 'Quantity Sold', 'Revenue'],
                    'rows' => $topProducts->map(fn ($item) => [
                        $item->product?->name ?? 'Unknown Product',
                        (float) $item->total_qty,
                        (float) $item->total_revenue,
                    ])->all(),
                ],
            ]
        );
    }

    public function render()
    {
        $sales = $this->salesQuery()
            ->latest()
            ->paginate(30);

        $summary = $this->summaryQuery();

        $topProducts = $this->topProductsQuery()->limit(10)->get();

        $branches = Branch::where('is_active', true)->get();

        return view('livewire.reports.sales-report', compact('sales', 'summary', 'topProducts', 'branches'))
            ->layout('layouts.app', ['title' => 'Sales Report']);
    }

    private function salesQuery()
    {
        return Sale::with(['customer', 'branch'])
            ->where('status', 'completed')
            ->when($this->branch_id, fn ($query) => $query->where('branch_id', $this->branch_id))
            ->when($this->dateFrom, fn ($query) => $query->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($query) => $query->whereDate('created_at', '<=', $this->dateTo));
    }

    private function summaryQuery()
    {
        return Sale::where('status', 'completed')
            ->when($this->branch_id, fn ($query) => $query->where('branch_id', $this->branch_id))
            ->when($this->dateFrom, fn ($query) => $query->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($query) => $query->whereDate('created_at', '<=', $this->dateTo))
            ->selectRaw('COUNT(*) as total_sales, SUM(total) as total_revenue, AVG(total) as avg_sale')
            ->first();
    }

    private function topProductsQuery()
    {
        return SaleItem::with('product')
            ->whereHas('sale', function ($query) {
                $query->where('status', 'completed')
                    ->when($this->branch_id, fn ($saleQuery) => $saleQuery->where('branch_id', $this->branch_id))
                    ->when($this->dateFrom, fn ($saleQuery) => $saleQuery->whereDate('created_at', '>=', $this->dateFrom))
                    ->when($this->dateTo, fn ($saleQuery) => $saleQuery->whereDate('created_at', '<=', $this->dateTo));
            })
            ->selectRaw('product_id, SUM(quantity) as total_qty, SUM(total_price) as total_revenue')
            ->groupBy('product_id')
            ->orderByDesc('total_revenue');
    }
}
