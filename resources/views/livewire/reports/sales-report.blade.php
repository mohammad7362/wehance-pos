<div>
    {{-- Filters --}}
    <div class="flex flex-wrap items-center gap-3 mb-4">
        <input wire:model.live="dateFrom" type="date" class="input-field" />
        <input wire:model.live="dateTo" type="date" class="input-field" />
        <select wire:model.live="branch_id" class="input-field">
            <option value="">All Branches</option>
            @foreach($branches as $b)
                <option value="{{ $b->id }}">{{ $b->name }}</option>
            @endforeach
        </select>
        <button wire:click="exportExcel" wire:loading.attr="disabled" class="btn-primary">
            Export Excel
        </button>
    </div>

    {{-- Summary --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="card p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Total Sales</p>
            <p class="text-2xl font-bold mt-1">{{ number_format($summary?->total_sales ?? 0) }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Total Revenue</p>
            <p class="text-2xl font-bold text-primary mt-1">${{ number_format($summary?->total_revenue ?? 0, 2) }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Avg Sale</p>
            <p class="text-2xl font-bold mt-1">${{ number_format($summary?->avg_sale ?? 0, 2) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        {{-- Top Products --}}
        <div class="card p-4">
            <h3 class="font-semibold mb-3">Top Products</h3>
            @forelse($topProducts as $tp)
                <div class="flex justify-between text-sm py-1.5 border-b last:border-0">
                    <span class="text-gray-700 truncate">{{ $tp->product?->name ?? '—' }}</span>
                    <span class="font-medium ml-2">${{ number_format($tp->total_revenue, 2) }}</span>
                </div>
            @empty
                <p class="text-gray-400 text-sm">No data.</p>
            @endforelse
        </div>

        {{-- Sales table --}}
        <div class="lg:col-span-2 card overflow-hidden">
            <table class="w-full text-sm">
                <thead class="table-header">
                    <tr>
                        <th class="px-4 py-3 text-left">Invoice</th>
                        <th class="px-4 py-3 text-left">Date</th>
                        <th class="px-4 py-3 text-left">Customer</th>
                        <th class="px-4 py-3 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($sales as $sale)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 font-mono text-primary text-xs">{{ $sale->reference_no }}</td>
                            <td class="px-4 py-2 text-gray-500 text-xs">{{ $sale->created_at->format('M d H:i') }}</td>
                            <td class="px-4 py-2">{{ $sale->customer?->name ?? 'Walk-in' }}</td>
                            <td class="px-4 py-2 text-right font-medium">${{ number_format($sale->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">No sales in range.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-4 py-3 border-t">{{ $sales->links() }}</div>
        </div>
    </div>
</div>
