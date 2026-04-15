<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Inventory;
use Carbon\Carbon;
use Livewire\Component;

class Dashboard extends Component
{
    public string $period = 'today';

    public function render()
    {
        $branchId = auth()->user()->branch_id;
        $range = $this->getDateRange();

        $salesQuery = Sale::where('branch_id', $branchId)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$range['from'], $range['to']]);

        $revenue   = (float) $salesQuery->sum('total');
        $salesCount = $salesQuery->count();
        $avgSale    = $salesCount > 0 ? $revenue / $salesCount : 0;
        $taxCollected = (float) $salesQuery->sum('tax_amount');

        $expenses = (float) Expense::where('branch_id', $branchId)
            ->whereBetween('date', [$range['from']->toDateString(), $range['to']->toDateString()])
            ->sum('amount');

        $newCustomers = Customer::whereBetween('created_at', [$range['from'], $range['to']])->count();

        $lowStockProducts = Product::whereHas('inventory', function ($q) use ($branchId) {
            $q->where('branch_id', $branchId)
              ->whereColumn('inventory.quantity', '<=', 'products.min_stock_alert');
        })->where('is_active', true)->count();

        $recentSales = Sale::with(['customer', 'items', 'cashier'])
            ->where('branch_id', $branchId)
            ->latest()
            ->take(8)
            ->get();

        $topProducts = \App\Models\SaleItem::selectRaw('product_id, product_name, SUM(quantity) as total_qty, SUM(total_price) as total_revenue')
            ->whereHas('sale', fn($q) => $q->where('branch_id', $branchId)->where('status', 'completed')->whereBetween('created_at', [$range['from'], $range['to']]))
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

        // Daily sales chart data (last 7 days)
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = Carbon::now()->subDays($i);
            $chartData[] = [
                'label' => $day->format('D'),
                'value' => (float) Sale::where('branch_id', $branchId)
                    ->where('status', 'completed')
                    ->whereDate('created_at', $day->toDateString())
                    ->sum('total'),
            ];
        }

        return view('livewire.dashboard', compact(
            'revenue', 'salesCount', 'avgSale', 'taxCollected',
            'expenses', 'newCustomers', 'lowStockProducts',
            'recentSales', 'topProducts', 'chartData'
        ))->layout('layouts.app');
    }

    private function getDateRange(): array
    {
        return match ($this->period) {
            'today'     => ['from' => Carbon::today(), 'to' => Carbon::now()],
            'yesterday' => ['from' => Carbon::yesterday(), 'to' => Carbon::yesterday()->endOfDay()],
            'week'      => ['from' => Carbon::now()->startOfWeek(), 'to' => Carbon::now()],
            'month'     => ['from' => Carbon::now()->startOfMonth(), 'to' => Carbon::now()],
            'year'      => ['from' => Carbon::now()->startOfYear(), 'to' => Carbon::now()],
            default     => ['from' => Carbon::today(), 'to' => Carbon::now()],
        };
    }
}
