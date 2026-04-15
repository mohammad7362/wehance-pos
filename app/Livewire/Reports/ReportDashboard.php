<?php

namespace App\Livewire\Reports;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Sale;
use App\Models\SaleItem;
use Livewire\Component;

class ReportDashboard extends Component
{
    public string $period    = 'month';
    public string $branch_id = '';

    public function render()
    {
        [$start, $end] = $this->getPeriodDates();

        $salesQuery = Sale::where('status', 'completed')
            ->when($this->branch_id, fn($q) => $q->where('branch_id', $this->branch_id))
            ->whereBetween('created_at', [$start, $end]);

        $totalRevenue  = (clone $salesQuery)->sum('total');
        $totalSales    = (clone $salesQuery)->count();
        $avgOrderValue = $totalSales > 0 ? $totalRevenue / $totalSales : 0;

        $cogs = SaleItem::whereHas('sale', function ($query) use ($start, $end) {
                $query->where('status', 'completed')
                    ->when($this->branch_id, fn($saleQuery) => $saleQuery->where('branch_id', $this->branch_id))
                    ->whereBetween('created_at', [$start, $end]);
            })
            ->selectRaw('SUM(quantity * cost_price) as cogs')
            ->value('cogs') ?? 0;

        $totalExpenses = Expense::when($this->branch_id, fn($q) => $q->where('branch_id', $this->branch_id))
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->sum('amount');

        $grossProfit = $totalRevenue - $cogs;
        $netProfit = $grossProfit - $totalExpenses;

        $newCustomers = Customer::whereBetween('created_at', [$start, $end])->count();

        // Daily chart (last 30 days)
        $chartData = Sale::where('status', 'completed')
            ->when($this->branch_id, fn($q) => $q->where('branch_id', $this->branch_id))
            ->whereBetween('created_at', [now()->subDays(29)->startOfDay(), now()->endOfDay()])
            ->selectRaw('DATE(created_at) as date, SUM(total) as total, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $labels     = [];
        $revenueArr = [];
        $countArr   = [];
        for ($i = 29; $i >= 0; $i--) {
            $d = now()->subDays($i)->toDateString();
            $labels[]     = now()->subDays($i)->format('M d');
            $revenueArr[] = $chartData[$d]->total ?? 0;
            $countArr[]   = $chartData[$d]->count ?? 0;
        }

        $branches = Branch::where('is_active', true)->get();

        return view('livewire.reports.report-dashboard', compact(
            'totalRevenue', 'totalSales', 'avgOrderValue', 'totalExpenses',
            'grossProfit', 'netProfit', 'newCustomers', 'labels', 'revenueArr', 'countArr', 'branches'
        ))->layout('layouts.app', ['title' => 'Reports Dashboard']);
    }

    private function getPeriodDates(): array
    {
        return match ($this->period) {
            'today'     => [now()->startOfDay(), now()->endOfDay()],
            'yesterday' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
            'week'      => [now()->startOfWeek(), now()->endOfWeek()],
            'month'     => [now()->startOfMonth(), now()->endOfMonth()],
            'year'      => [now()->startOfYear(), now()->endOfYear()],
            default     => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }
}
