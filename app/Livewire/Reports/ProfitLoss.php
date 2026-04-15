<?php

namespace App\Livewire\Reports;

use App\Models\Branch;
use App\Models\Expense;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Support\Exports\ExcelReportExporter;
use Livewire\Component;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProfitLoss extends Component
{
    public string $dateFrom  = '';
    public string $dateTo    = '';
    public string $branch_id = '';

    public function mount(): void
    {
        $this->dateFrom = now()->startOfMonth()->toDateString();
        $this->dateTo   = now()->toDateString();
    }

    public function exportExcel(): BinaryFileResponse
    {
        $report = $this->reportData();

        return app(ExcelReportExporter::class)->download(
            'profit-loss-' . now()->format('Ymd-His') . '.xlsx',
            [
                [
                    'title' => 'Statement',
                    'headings' => ['Metric', 'Value'],
                    'rows' => [
                        ['Date From', $this->dateFrom ?: ''],
                        ['Date To', $this->dateTo ?: ''],
                        ['Branch', optional(Branch::find($this->branch_id))->name ?? 'All Branches'],
                        ['Revenue', (float) $report['revenue']],
                        ['Tax Collected', (float) $report['totalTax']],
                        ['Discounts Given', (float) $report['totalDiscount']],
                        ['COGS', (float) $report['cogs']],
                        ['Gross Profit', (float) $report['grossProfit']],
                        ['Total Expenses', (float) $report['totalExpenses']],
                        ['Net Profit', (float) $report['netProfit']],
                    ],
                ],
                [
                    'title' => 'Expenses',
                    'headings' => ['Category', 'Amount'],
                    'rows' => $report['expenseByCategory']->map(fn ($expense) => [
                        $expense->category?->name ?? 'Uncategorized',
                        (float) $expense->total,
                    ])->all(),
                ],
            ]
        );
    }

    public function render()
    {
        $report = $this->reportData();

        $branches = Branch::where('is_active', true)->get();

        return view('livewire.reports.profit-loss', compact(
            'branches'
        ) + $report)->layout('layouts.app', ['title' => 'Profit & Loss']);
    }

    private function reportData(): array
    {
        $salesQuery = Sale::where('status', 'completed')
            ->when($this->branch_id, fn ($query) => $query->where('branch_id', $this->branch_id))
            ->when($this->dateFrom, fn ($query) => $query->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($query) => $query->whereDate('created_at', '<=', $this->dateTo));

        $revenue = (clone $salesQuery)->sum('total');
        $totalTax = (clone $salesQuery)->sum('tax_amount');
        $totalDiscount = (clone $salesQuery)->sum('discount_amount');

        $cogs = SaleItem::whereHas('sale', function ($query) {
            $query->where('status', 'completed')
                ->when($this->branch_id, fn ($saleQuery) => $saleQuery->where('branch_id', $this->branch_id))
                ->when($this->dateFrom, fn ($saleQuery) => $saleQuery->whereDate('created_at', '>=', $this->dateFrom))
                ->when($this->dateTo, fn ($saleQuery) => $saleQuery->whereDate('created_at', '<=', $this->dateTo));
        })
            ->selectRaw('SUM(quantity * cost_price) as cogs')
            ->value('cogs') ?? 0;

        $grossProfit = $revenue - $cogs;

        $totalExpenses = Expense::when($this->branch_id, fn ($query) => $query->where('branch_id', $this->branch_id))
            ->when($this->dateFrom, fn ($query) => $query->whereDate('date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($query) => $query->whereDate('date', '<=', $this->dateTo))
            ->sum('amount');

        $netProfit = $grossProfit - $totalExpenses;

        $expenseByCategory = Expense::with('category')
            ->when($this->branch_id, fn ($query) => $query->where('branch_id', $this->branch_id))
            ->when($this->dateFrom, fn ($query) => $query->whereDate('date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($query) => $query->whereDate('date', '<=', $this->dateTo))
            ->selectRaw('expense_category_id, SUM(amount) as total')
            ->groupBy('expense_category_id')
            ->with('category')
            ->get();

        return compact(
            'revenue',
            'totalTax',
            'totalDiscount',
            'cogs',
            'grossProfit',
            'totalExpenses',
            'netProfit',
            'expenseByCategory'
        );
    }
}
