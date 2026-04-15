<div>
    <div class="flex flex-wrap items-center gap-3 mb-6">
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

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="card p-6">
            <h3 class="font-bold text-lg mb-4">Income Statement</h3>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between font-semibold text-base">
                    <span>Revenue</span>
                    <span class="text-green-600">${{ number_format($revenue, 2) }}</span>
                </div>
                <div class="flex justify-between text-gray-500 pl-4">
                    <span>Discounts Given</span>
                    <span class="text-red-500">-${{ number_format($totalDiscount, 2) }}</span>
                </div>
                <div class="flex justify-between text-gray-500 pl-4">
                    <span>Tax Collected</span>
                    <span>${{ number_format($totalTax, 2) }}</span>
                </div>
                <div class="border-t pt-2 flex justify-between text-gray-600">
                    <span>Cost of Goods Sold (COGS)</span>
                    <span class="text-red-500">-${{ number_format($cogs, 2) }}</span>
                </div>
                <div class="flex justify-between font-semibold border-t pt-2">
                    <span>Gross Profit</span>
                    <span class="{{ $grossProfit >= 0 ? 'text-green-600' : 'text-red-600' }}">${{ number_format($grossProfit, 2) }}</span>
                </div>
                <div class="pt-2">
                    <div class="flex justify-between text-gray-600 font-medium">
                        <span>Operating Expenses</span>
                        <span class="text-red-500">-${{ number_format($totalExpenses, 2) }}</span>
                    </div>
                </div>
                <div class="flex justify-between font-bold text-lg border-t-2 border-gray-800 pt-2">
                    <span>Net Profit</span>
                    <span class="{{ $netProfit >= 0 ? 'text-green-600' : 'text-red-600' }}">${{ number_format($netProfit, 2) }}</span>
                </div>
            </div>
        </div>

        <div class="card p-6">
            <h3 class="font-bold text-lg mb-4">Expenses by Category</h3>
            @forelse($expenseByCategory as $ec)
                <div class="flex justify-between items-center py-2 border-b last:border-0">
                    <span class="text-gray-700">{{ $ec->category?->name ?? 'Uncategorized' }}</span>
                    <span class="font-semibold text-red-500">${{ number_format($ec->total, 2) }}</span>
                </div>
            @empty
                <p class="text-gray-400 text-sm">No expenses in this period.</p>
            @endforelse

            @if($totalExpenses > 0)
                <div class="flex justify-between items-center py-2 border-t-2 mt-2 font-bold">
                    <span>Total Expenses</span>
                    <span class="text-red-600">${{ number_format($totalExpenses, 2) }}</span>
                </div>
            @endif
        </div>
    </div>
</div>
