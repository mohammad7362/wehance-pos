<div>
{{-- Period Selector --}}
<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <div class="flex items-center gap-3">
        <h2 class="text-2xl font-bold text-slate-800">Overview</h2>
        <span class="text-sm text-slate-500">{{ now()->format('l, F j, Y') }}</span>
    </div>
    <div class="flex gap-1.5 bg-white border border-slate-200 rounded-xl p-1 shadow-sm">
        @foreach(['today' => 'Today', 'yesterday' => 'Yesterday', 'week' => 'This Week', 'month' => 'This Month', 'year' => 'This Year'] as $key => $label)
        <button wire:click="$set('period', '{{ $key }}')"
            class="px-3 py-1.5 text-sm font-medium rounded-lg transition-colors
                   {{ $period === $key ? 'bg-blue-600 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
            {{ $label }}
        </button>
        @endforeach
    </div>
</div>

{{-- KPI Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-medium text-slate-500">Revenue</span>
            <div class="w-9 h-9 bg-blue-100 rounded-xl flex items-center justify-center">
                <x-pos-icon name="currency-dollar" class="w-5 h-5 text-blue-600" />
            </div>
        </div>
        <p class="text-2xl font-bold text-slate-800">${{ number_format($revenue, 2) }}</p>
        <p class="text-xs text-slate-500 mt-1">{{ $salesCount }} transaction{{ $salesCount !== 1 ? 's' : '' }}</p>
    </div>

    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-medium text-slate-500">Avg. Sale</span>
            <div class="w-9 h-9 bg-green-100 rounded-xl flex items-center justify-center">
                <x-pos-icon name="trending-up" class="w-5 h-5 text-green-600" />
            </div>
        </div>
        <p class="text-2xl font-bold text-slate-800">${{ number_format($avgSale, 2) }}</p>
        <p class="text-xs text-slate-500 mt-1">Per transaction</p>
    </div>

    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-medium text-slate-500">Expenses</span>
            <div class="w-9 h-9 bg-red-100 rounded-xl flex items-center justify-center">
                <x-pos-icon name="banknotes" class="w-5 h-5 text-red-600" />
            </div>
        </div>
        <p class="text-2xl font-bold text-slate-800">${{ number_format($expenses, 2) }}</p>
        <p class="text-xs {{ ($revenue - $expenses) >= 0 ? 'text-green-600' : 'text-red-600' }} mt-1 font-medium">
            Net: ${{ number_format($revenue - $expenses, 2) }}
        </p>
    </div>

    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-medium text-slate-500">New Customers</span>
            <div class="w-9 h-9 bg-purple-100 rounded-xl flex items-center justify-center">
                <x-pos-icon name="users" class="w-5 h-5 text-purple-600" />
            </div>
        </div>
        <p class="text-2xl font-bold text-slate-800">{{ $newCustomers }}</p>
        <p class="text-xs text-slate-500 mt-1">Tax: ${{ number_format($taxCollected, 2) }}</p>
    </div>
</div>

{{-- Low Stock Alert --}}
@if($lowStockProducts > 0)
<div class="flex items-center gap-3 bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6 text-sm">
    <x-pos-icon name="exclamation-triangle" class="w-5 h-5 text-amber-500 flex-shrink-0" />
    <span class="text-amber-800 font-medium">{{ $lowStockProducts }} product{{ $lowStockProducts > 1 ? 's are' : ' is' }} running low on stock.</span>
    <a href="{{ route('inventory.index') }}" class="ml-auto text-amber-700 hover:text-amber-900 font-medium underline">View Inventory</a>
</div>
@endif

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- Sales Chart --}}
    <div class="xl:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-slate-800">Sales – Last 7 Days</h3>
        </div>
        <div class="flex items-end gap-2 h-36" x-data>
            @php $maxVal = max(array_column($chartData, 'value')) ?: 1; @endphp
            @foreach($chartData as $day)
            <div class="flex-1 flex flex-col items-center gap-1">
                <span class="text-xs text-slate-500 font-medium">${{ $day['value'] > 0 ? number_format($day['value'], 0) : '' }}</span>
                <div class="w-full bg-blue-100 rounded-t-md transition-all duration-500"
                     style="height: {{ max(4, ($day['value'] / $maxVal) * 100) }}px; min-height: 4px;"
                     title="${{ number_format($day['value'], 2) }}">
                    <div class="w-full h-full bg-blue-500 rounded-t-md opacity-80 hover:opacity-100 transition-opacity"></div>
                </div>
                <span class="text-xs text-slate-500">{{ $day['label'] }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Top Products --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
        <h3 class="font-semibold text-slate-800 mb-4">Top Products</h3>
        @forelse($topProducts as $i => $product)
        <div class="flex items-center gap-3 py-2 {{ !$loop->last ? 'border-b border-slate-100' : '' }}">
            <span class="w-6 h-6 flex items-center justify-center text-xs font-bold rounded-full
                         {{ $i === 0 ? 'bg-yellow-100 text-yellow-700' : ($i === 1 ? 'bg-slate-100 text-slate-600' : 'bg-orange-100 text-orange-700') }}">
                {{ $i + 1 }}
            </span>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-slate-700 truncate">{{ $product->product_name }}</p>
                <p class="text-xs text-slate-500">{{ number_format($product->total_qty, 0) }} units</p>
            </div>
            <span class="text-sm font-semibold text-slate-800">${{ number_format($product->total_revenue, 0) }}</span>
        </div>
        @empty
        <p class="text-sm text-slate-400 text-center py-8">No sales data yet</p>
        @endforelse
    </div>

    {{-- Recent Sales --}}
    <div class="xl:col-span-3 bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="flex items-center justify-between p-5 border-b border-slate-100">
            <h3 class="font-semibold text-slate-800">Recent Transactions</h3>
            <a href="{{ route('sales.index') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">View all →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
                        <th class="text-left px-5 py-3 font-medium">Reference</th>
                        <th class="text-left px-5 py-3 font-medium">Customer</th>
                        <th class="text-left px-5 py-3 font-medium">Cashier</th>
                        <th class="text-left px-5 py-3 font-medium">Items</th>
                        <th class="text-right px-5 py-3 font-medium">Total</th>
                        <th class="text-left px-5 py-3 font-medium">Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentSales as $sale)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-3">
                            <a href="{{ route('sales.show', $sale) }}" class="font-medium text-blue-600 hover:text-blue-700">
                                {{ $sale->reference_no }}
                            </a>
                        </td>
                        <td class="px-5 py-3 text-slate-600">{{ $sale->customer?->name ?? 'Walk-in' }}</td>
                        <td class="px-5 py-3 text-slate-600">{{ $sale->cashier?->name ?? '–' }}</td>
                        <td class="px-5 py-3 text-slate-600">{{ $sale->items->count() }}</td>
                        <td class="px-5 py-3 text-right font-semibold text-slate-800">${{ number_format($sale->total, 2) }}</td>
                        <td class="px-5 py-3 text-slate-500">{{ $sale->created_at->format('h:i A') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-slate-400">No transactions yet</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>
